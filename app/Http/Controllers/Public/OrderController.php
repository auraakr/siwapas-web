<?php

namespace App\Http\Controllers\Public;

use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function show(Order $order): Response
    {
        $order->load(['payments', 'reservation.facility', 'eventRegistration.event']);

        $payments = $order->payments->sortByDesc('id')->values();
        $reservation = $order->reservation;
        $event = $order->eventRegistration?->event;

        return Inertia::render('public/orders/show', [
            'order' => [
                'code' => $order->code,
                'access_token' => $order->access_token,
                'kind' => $order->kind->value,
                'status' => $order->status->value,
                'customer_name' => $order->customer_name,
                'payment_plan' => $order->requiresDownPayment() ? 'down_payment' : 'full',
                'subtotal' => $order->subtotal,
                'unique_code' => $order->unique_code,
                'total' => $order->total,
                'dp_percentage' => $order->dp_percentage,
                'dp_amount' => $order->dp_amount,
                'remaining_amount' => $order->remaining_amount,
                'first_payment_amount' => $order->firstPaymentAmount(),
                'expires_at' => $order->expires_at?->toIso8601String(),
                'can_upload_proof' => $order->acceptsFirstPaymentProof(),
                'can_upload_settlement' => $order->acceptsSettlementProof(),
                'settlement_closed' => $order->status === OrderStatus::DpPaid && ! $order->acceptsSettlementProof(),
                'settled_at' => $order->settled_at?->toIso8601String(),
                'settlement_method' => $order->settlement_method?->value,
                'cancelled_by' => $order->cancelled_by?->value,
                'rejection_reason' => $this->rejectionToShow($order, $payments),
                'item' => $reservation
                    ? [
                        'title' => $reservation->facility->name,
                        'check_in' => $reservation->check_in->toDateString(),
                        'check_out' => $reservation->check_out->toDateString(),
                        'guests' => $reservation->guests,
                    ]
                    : ($event ? ['title' => $event->title] : null),
            ],
            'payment_methods' => $payments->last()->bank_snapshot ?? [],
            'verification_hours' => config('siwapas.payment.verification_target_hours'),
        ]);
    }

    /**
     * The reason of the last decided payment when it was rejected and the visitor still has to act on
     * it (upload again) or should know why the order closed.
     *
     * @param  Collection<int, Payment>  $payments  Newest first.
     */
    private function rejectionToShow(Order $order, Collection $payments): ?string
    {
        if (! in_array($order->status, [OrderStatus::PendingPayment, OrderStatus::DpPaid, OrderStatus::Rejected, OrderStatus::Expired], true)) {
            return null;
        }

        $lastDecided = $payments->first(fn (Payment $payment): bool => $payment->status !== PaymentStatus::Pending);

        return $lastDecided?->status === PaymentStatus::Rejected ? $lastDecided->rejection_reason : null;
    }
}
