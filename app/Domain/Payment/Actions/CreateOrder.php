<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\DTOs\CustomerData;
use App\Domain\Payment\Enums\OrderKind;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Enums\PaymentType;
use App\Domain\Payment\Exceptions\PaymentNotPossible;
use App\Domain\Payment\Models\BankAccount;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Services\DownPaymentCalculator;
use App\Domain\Payment\Services\OrderCodeGenerator;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrder
{
    private const CODE_ATTEMPTS = 5;

    public function __construct(
        private OrderCodeGenerator $codes,
        private DownPaymentCalculator $downPayments,
    ) {}

    /**
     * Create an order with its first pending manual-transfer payment.
     * Call inside the caller's transaction so inventory and order stay together.
     *
     * With a down payment percentage the first payment is the down payment and the rest is settled
     * later; without one the order is paid in full. The unique code is added to the first transfer only.
     * The payment deadline is the hold period, or $payBefore when that comes sooner.
     */
    public function handle(
        OrderKind $kind,
        CustomerData $customer,
        int $subtotal,
        ?int $downPaymentPercentage = null,
        ?CarbonInterface $payBefore = null,
    ): Order {
        $methods = BankAccount::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();

        if ($methods->isEmpty()) {
            throw PaymentNotPossible::noPaymentMethod();
        }

        $uniqueCode = random_int(
            (int) config('siwapas.payment.unique_code_min'),
            (int) config('siwapas.payment.unique_code_max'),
        );

        $split = $downPaymentPercentage !== null
            ? $this->downPayments->split($subtotal, $downPaymentPercentage)
            : null;

        $expiresAt = now()->addHours((int) config('siwapas.payment.hold_hours'));

        if ($payBefore !== null && $payBefore->lt($expiresAt)) {
            $expiresAt = $payBefore->toImmutable()->utc();
        }

        for ($attempt = 1; ; $attempt++) {
            try {
                $order = DB::transaction(fn (): Order => Order::create([
                    'code' => $this->codes->next(),
                    'access_token' => Str::random(48),
                    'kind' => $kind,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'customer_email' => $customer->email,
                    'notes' => $customer->notes,
                    'status' => OrderStatus::PendingPayment,
                    'subtotal' => $subtotal,
                    'unique_code' => $uniqueCode,
                    'total' => $subtotal + $uniqueCode,
                    'dp_percentage' => $split !== null ? $downPaymentPercentage : null,
                    'dp_amount' => $split['dp_amount'] ?? null,
                    'remaining_amount' => $split['remaining_amount'] ?? null,
                    'expires_at' => $expiresAt,
                ]));

                break;
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt >= self::CODE_ATTEMPTS) {
                    throw $e;
                }
            }
        }

        $order->statusHistories()->create([
            'from_status' => null,
            'to_status' => OrderStatus::PendingPayment,
            'actor_type' => 'visitor',
        ]);

        $order->payments()->create([
            'type' => $split !== null ? PaymentType::DownPayment : PaymentType::Full,
            'method' => Payment::METHOD_TRANSFER,
            'bank_account_id' => $methods->first()->id,
            'bank_snapshot' => $methods->map(fn (BankAccount $method): array => $method->snapshot())->all(),
            'expected_amount' => $order->firstPaymentAmount(),
            'status' => PaymentStatus::Pending,
        ]);

        return $order;
    }
}
