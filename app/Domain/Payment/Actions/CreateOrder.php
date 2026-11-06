<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\DTOs\CustomerData;
use App\Domain\Payment\Enums\OrderKind;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Exceptions\PaymentNotPossible;
use App\Domain\Payment\Models\BankAccount;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Services\OrderCodeGenerator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrder
{
    private const CODE_ATTEMPTS = 5;

    public function __construct(private OrderCodeGenerator $codes) {}

    /**
     * Create an order with its pending manual-transfer payment.
     * Call inside the caller's transaction so inventory and order stay together.
     */
    public function handle(OrderKind $kind, CustomerData $customer, int $subtotal): Order
    {
        $methods = BankAccount::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();

        if ($methods->isEmpty()) {
            throw PaymentNotPossible::noPaymentMethod();
        }

        $uniqueCode = random_int(
            (int) config('siwapas.payment.unique_code_min'),
            (int) config('siwapas.payment.unique_code_max'),
        );
        $total = $subtotal + $uniqueCode;

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
                    'total' => $total,
                    'expires_at' => now()->addHours((int) config('siwapas.payment.hold_hours')),
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
            'method' => 'manual_transfer',
            'bank_account_id' => $methods->first()->id,
            'bank_snapshot' => $methods->map(fn (BankAccount $method): array => $method->snapshot())->all(),
            'expected_amount' => $total,
            'status' => PaymentStatus::Pending,
        ]);

        return $order;
    }
}
