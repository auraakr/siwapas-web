<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Booking\Models\Reservation;
use App\Domain\Payment\Actions\CancelOrder;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Exceptions\InvalidOrderTransition;
use App\Domain\Payment\Models\OrderStatusHistory;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Models\PaymentProof;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CancelOrderRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
        ]);

        $reservations = Reservation::query()
            ->with(['order', 'facility'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->whereHas('order', fn ($order) => $order->where('status', $status)))
            ->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Reservation $reservation): array => [
                'id' => $reservation->id,
                'order_code' => $reservation->order->code,
                'status' => $reservation->order->status->value,
                'customer_name' => $reservation->order->customer_name,
                'customer_phone' => $reservation->order->customer_phone,
                'facility' => $reservation->facility->name,
                'check_in' => $reservation->check_in->toDateString(),
                'check_out' => $reservation->check_out->toDateString(),
                'guests' => $reservation->guests,
                'total' => $reservation->order->total,
            ]);

        return Inertia::render('admin/reservations/index', [
            'reservations' => $reservations,
            'status' => $filters['status'] ?? null,
            'statuses' => array_map(fn (OrderStatus $status): string => $status->value, OrderStatus::cases()),
        ]);
    }

    public function show(Reservation $reservation): Response
    {
        $reservation->load(['order.payments.proofs', 'order.statusHistories', 'facility']);
        $order = $reservation->order;

        return Inertia::render('admin/reservations/show', [
            'reservation' => [
                'id' => $reservation->id,
                'facility' => $reservation->facility->name,
                'check_in' => $reservation->check_in->toDateString(),
                'check_out' => $reservation->check_out->toDateString(),
                'guests' => $reservation->guests,
                'is_active' => $reservation->is_active,
                'price_breakdown' => $reservation->price_breakdown ?? [],
            ],
            'order' => [
                'code' => $order->code,
                'status' => $order->status->value,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_email' => $order->customer_email,
                'notes' => $order->notes,
                'subtotal' => $order->subtotal,
                'unique_code' => $order->unique_code,
                'total' => $order->total,
                'created_at' => $order->created_at?->toIso8601String(),
                'confirmed_at' => $order->confirmed_at?->toIso8601String(),
                'can_cancel' => $order->status->canTransitionTo(OrderStatus::Cancelled),
            ],
            'payments' => $order->payments->map(fn (Payment $payment): array => [
                'id' => $payment->id,
                'status' => $payment->status->value,
                'expected_amount' => $payment->expected_amount,
                'rejection_reason' => $payment->rejection_reason,
                'proofs' => $payment->proofs->map(fn (PaymentProof $proof): array => [
                    'name' => $proof->original_name,
                    'sender_name' => $proof->sender_name,
                    'claimed_amount' => $proof->claimed_amount,
                    'url' => URL::temporarySignedRoute('admin.proofs.show', now()->addMinutes(5), ['proof' => $proof->id]),
                ])->values(),
            ])->values(),
            'history' => $order->statusHistories->sortBy('id')->map(fn (OrderStatusHistory $row): array => [
                'from' => $row->from_status?->value,
                'to' => $row->to_status->value,
                'actor' => $row->actor_type,
                'note' => $row->note,
                'at' => $row->created_at?->toIso8601String(),
            ])->values(),
        ]);
    }

    public function cancel(CancelOrderRequest $request, Reservation $reservation, CancelOrder $cancelOrder): RedirectResponse
    {
        try {
            $cancelOrder->handle($reservation->order, $request->user(), $request->string('reason')->toString());
        } catch (InvalidOrderTransition) {
            return back()->withErrors(['reason' => 'Reservasi ini tidak dapat dibatalkan lagi.']);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Reservasi dibatalkan.']);

        return back();
    }
}
