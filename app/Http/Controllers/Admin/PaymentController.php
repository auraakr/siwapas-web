<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Payment\Actions\RejectPayment;
use App\Domain\Payment\Actions\VerifyPayment;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Exceptions\InvalidOrderTransition;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Models\PaymentProof;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectPaymentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(): Response
    {
        $payments = Payment::query()
            ->where('status', PaymentStatus::Pending)
            ->whereHas('order', fn ($query) => $query->where('status', OrderStatus::AwaitingVerification))
            ->with(['order', 'proofs'])
            ->oldest()
            ->limit(50)
            ->get();

        return Inertia::render('admin/payments/index', [
            'payments' => $payments->map(fn (Payment $payment): array => [
                'id' => $payment->id,
                'order_code' => $payment->order->code,
                'customer_name' => $payment->order->customer_name,
                'customer_phone' => $payment->order->customer_phone,
                'expected_amount' => $payment->expected_amount,
                'proofs' => $payment->proofs->map(fn (PaymentProof $proof): array => [
                    'name' => $proof->original_name,
                    'sender_name' => $proof->sender_name,
                    'claimed_amount' => $proof->claimed_amount,
                    'url' => URL::temporarySignedRoute('admin.proofs.show', now()->addMinutes(5), ['proof' => $proof->id]),
                ])->values(),
            ])->values(),
        ]);
    }

    public function verify(Request $request, Payment $payment, VerifyPayment $verifyPayment): RedirectResponse
    {
        try {
            $verifyPayment->handle($payment, $request->user());
        } catch (InvalidOrderTransition) {
            return back()->withErrors(['payment' => 'Pembayaran ini sudah diproses.']);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pembayaran diverifikasi.']);

        return back();
    }

    public function reject(RejectPaymentRequest $request, Payment $payment, RejectPayment $rejectPayment): RedirectResponse
    {
        try {
            $rejectPayment->handle($payment, $request->user(), $request->string('reason')->toString());
        } catch (InvalidOrderTransition) {
            return back()->withErrors(['payment' => 'Pembayaran ini sudah diproses.']);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pembayaran ditolak.']);

        return back();
    }
}
