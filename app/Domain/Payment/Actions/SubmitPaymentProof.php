<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Exceptions\PaymentNotPossible;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\PaymentProof;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubmitPaymentProof
{
    public function __construct(private TransitionOrderStatus $transition) {}

    public function handle(
        Order $order,
        UploadedFile $file,
        ?string $senderName = null,
        ?CarbonInterface $transferDate = null,
        ?int $claimedAmount = null,
    ): PaymentProof {
        if ($order->status !== OrderStatus::PendingPayment || ($order->expires_at !== null && $order->expires_at->isPast())) {
            throw PaymentNotPossible::orderNotPayable();
        }

        $payment = $order->payments()->where('status', PaymentStatus::Pending)->latest('id')->firstOrFail();
        $disk = (string) config('siwapas.payment.proof_disk');
        $path = Storage::disk($disk)->putFileAs(
            'payment-proofs/'.$order->code,
            $file,
            Str::random(40).'.'.$file->guessExtension(),
        );

        return DB::transaction(function () use ($order, $payment, $file, $disk, $path, $senderName, $transferDate, $claimedAmount): PaymentProof {
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

            $this->transition->handle($order, OrderStatus::AwaitingVerification, actorType: 'visitor', note: 'Payment proof uploaded.');

            return $proof;
        });
    }
}
