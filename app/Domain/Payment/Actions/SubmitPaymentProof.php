<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Enums\PaymentType;
use App\Domain\Payment\Exceptions\PaymentNotPossible;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Models\PaymentProof;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SubmitPaymentProof
{
    public function __construct(private TransitionOrderStatus $transition) {}

    /**
     * Attach a transfer proof to the payment that is due now:
     * - the down payment or full payment, while the order waits for it and its deadline has not passed;
     * - the settlement, once the down payment is verified and until the end of the check-out day.
     */
    public function handle(
        Order $order,
        UploadedFile $file,
        ?string $senderName = null,
        ?CarbonInterface $transferDate = null,
        ?int $claimedAmount = null,
    ): PaymentProof {
        $order->loadMissing('reservation');

        if (! $order->acceptsFirstPaymentProof() && ! $order->acceptsSettlementProof()) {
            throw PaymentNotPossible::orderNotPayable();
        }

        $disk = (string) config('siwapas.payment.proof_disk');
        $path = Storage::disk($disk)->putFileAs(
            'payment-proofs/'.$order->code,
            $file,
            Str::random(40).'.'.$file->guessExtension(),
        );

        if ($path === false) {
            throw new RuntimeException('The payment proof could not be stored.');
        }

        try {
            return DB::transaction(function () use ($order, $file, $disk, $path, $senderName, $transferDate, $claimedAmount): PaymentProof {
                $locked = Order::query()->with('reservation')->whereKey($order->getKey())->lockForUpdate()->firstOrFail();

                [$payment, $nextStatus, $note] = match (true) {
                    $locked->acceptsFirstPaymentProof() => [
                        $locked->payments()->where('status', PaymentStatus::Pending)->latest('id')->firstOrFail(),
                        OrderStatus::AwaitingVerification,
                        $locked->requiresDownPayment() ? 'Down payment proof uploaded.' : 'Payment proof uploaded.',
                    ],
                    $locked->acceptsSettlementProof() => [
                        $this->pendingSettlement($locked),
                        OrderStatus::AwaitingSettlementVerification,
                        'Settlement proof uploaded.',
                    ],
                    default => throw PaymentNotPossible::orderNotPayable(),
                };

                $proof = $payment->proofs()->create([
                    'disk' => $disk,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => (string) $file->getMimeType(),
                    'size' => (int) $file->getSize(),
                    'sender_name' => $senderName,
                    'transfer_date' => $transferDate,
                    'claimed_amount' => $claimedAmount,
                    'uploaded_at' => now(),
                ]);

                $this->transition->handle($order, $nextStatus, actorType: 'visitor', note: $note);

                return $proof;
            });
        } catch (Throwable $e) {
            Storage::disk($disk)->delete($path);

            throw $e;
        }
    }

    /**
     * The open settlement payment, created on the first settlement upload for the remaining amount.
     */
    private function pendingSettlement(Order $order): Payment
    {
        $existing = $order->payments()
            ->where('type', PaymentType::Settlement)
            ->where('status', PaymentStatus::Pending)
            ->latest('id')
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $downPayment = $order->latestPaymentOf(PaymentType::DownPayment);

        return $order->payments()->create([
            'type' => PaymentType::Settlement,
            'method' => Payment::METHOD_TRANSFER,
            'bank_account_id' => $downPayment?->bank_account_id,
            'bank_snapshot' => $downPayment?->bank_snapshot,
            'expected_amount' => (int) $order->remaining_amount,
            'status' => PaymentStatus::Pending,
        ]);
    }
}
