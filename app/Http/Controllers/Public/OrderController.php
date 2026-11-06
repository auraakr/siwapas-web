<?php

namespace App\Http\Controllers\Public;

use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Models\Order;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function show(Order $order): Response
    {
        $order->load(['payments', 'reservation.facility', 'eventRegistration.event']);

        $payment = $order->payments->sortByDesc('id')->first();
        $reservation = $order->reservation;
        $event = $order->eventRegistration?->event;

        return Inertia::render('public/orders/show', [
            'order' => [
                'code' => $order->code,
                'access_token' => $order->access_token,
                'kind' => $order->kind->value,
                'status' => $order->status->value,
                'customer_name' => $order->customer_name,
                'subtotal' => $order->subtotal,
                'unique_code' => $order->unique_code,
                'total' => $order->total,
                'expires_at' => $order->expires_at?->toIso8601String(),
                'can_upload_proof' => $order->status === OrderStatus::PendingPayment
                    && ($order->expires_at === null || $order->expires_at->isFuture()),
                'rejection_reason' => $payment?->rejection_reason,
                'item' => $reservation
                    ? [
                        'title' => $reservation->facility->name,
                        'check_in' => $reservation->check_in->toDateString(),
                        'check_out' => $reservation->check_out->toDateString(),
                        'guests' => $reservation->guests,
                    ]
                    : ($event ? ['title' => $event->title] : null),
            ],
            'payment_methods' => $payment->bank_snapshot ?? [],
            'verification_hours' => config('siwapas.payment.verification_target_hours'),
        ]);
    }
}
