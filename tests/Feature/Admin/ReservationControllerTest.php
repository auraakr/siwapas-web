<?php

use App\Domain\Booking\Models\Reservation;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Models\PaymentProof;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function reservationWithOrder(array $order = []): Reservation
{
    return Reservation::factory()->create(['order_id' => Order::factory()->create($order)->id]);
}

it('redirects guests to the login page', function () {
    $reservation = reservationWithOrder();

    $this->get(route('admin.reservations.index'))->assertRedirect(route('login'));
    $this->get(route('admin.reservations.show', $reservation))->assertRedirect(route('login'));
    $this->post(route('admin.reservations.cancel', $reservation), ['reason' => 'x'])->assertRedirect(route('login'));
});

it('forbids registered visitors who are not admins', function () {
    $reservation = reservationWithOrder();
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.reservations.index'))->assertForbidden();
    $this->get(route('admin.reservations.show', $reservation))->assertForbidden();
    $this->post(route('admin.reservations.cancel', $reservation), ['reason' => 'x'])->assertForbidden();

    expect($reservation->order->fresh()->status)->toBe(OrderStatus::PendingPayment);
});

describe('index', function () {
    it('lists reservations with their order details, newest first', function () {
        reservationWithOrder(['code' => 'SWP-2611-0001', 'customer_name' => 'Lama']);
        reservationWithOrder(['code' => 'SWP-2611-0002', 'customer_name' => 'Baru']);
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.reservations.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/reservations/index')
                ->has('reservations.data', 2)
                ->where('reservations.data.0.order_code', 'SWP-2611-0002')
                ->where('reservations.data.1.customer_name', 'Lama'));
    });

    it('filters by order status', function () {
        reservationWithOrder(['code' => 'SWP-2611-0001']);
        reservationWithOrder(['code' => 'SWP-2611-0002', 'status' => OrderStatus::Confirmed]);
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.reservations.index', ['status' => 'confirmed']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('reservations.data', 1)
                ->where('reservations.data.0.order_code', 'SWP-2611-0002')
                ->where('status', 'confirmed'));
    });

    it('rejects an unknown status filter', function () {
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.reservations.index', ['status' => 'bogus']))->assertSessionHasErrors('status');
    });

    it('paginates fifteen reservations per page', function () {
        Reservation::factory()->count(16)->create();
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.reservations.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('reservations.data', 15)
                ->where('reservations.total', 16)
                ->whereNot('reservations.next_page_url', null));
    });
});

describe('show', function () {
    it('shows the reservation, payment proofs and status history', function () {
        $reservation = reservationWithOrder(['status' => OrderStatus::AwaitingVerification]);
        $payment = Payment::factory()->for($reservation->order)->create();
        PaymentProof::factory()->for($payment)->create(['original_name' => 'bukti.jpg']);
        $reservation->order->statusHistories()->create(['from_status' => null, 'to_status' => OrderStatus::PendingPayment, 'actor_type' => 'visitor']);
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.reservations.show', $reservation))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/reservations/show')
                ->where('order.code', $reservation->order->code)
                ->where('order.can_cancel', true)
                ->where('payments.0.proofs.0.name', 'bukti.jpg')
                ->where('payments.0.proofs.0.url', fn (string $url) => str_contains($url, 'signature='))
                ->where('history.0.to', 'pending_payment'));
    });

    it('does not offer cancelling an order that is already closed', function () {
        $reservation = reservationWithOrder(['status' => OrderStatus::Expired]);
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.reservations.show', $reservation))
            ->assertInertia(fn (Assert $page) => $page->where('order.can_cancel', false));
    });
});

describe('cancel', function () {
    it('cancels the order, releases the slot and records the admin and reason', function () {
        $reservation = reservationWithOrder(['status' => OrderStatus::Confirmed]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.reservations.cancel', $reservation), ['reason' => 'Tamu membatalkan'])
            ->assertRedirect();

        $history = $reservation->order->statusHistories()->sole();
        expect($reservation->order->fresh()->status)->toBe(OrderStatus::Cancelled)
            ->and($reservation->fresh()->is_active)->toBeFalse()
            ->and($history->actor_id)->toBe($admin->id)
            ->and($history->note)->toBe('Tamu membatalkan');
    });

    it('requires a reason', function () {
        $reservation = reservationWithOrder();
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('admin.reservations.cancel', $reservation), [])
            ->assertSessionHasErrors(['reason' => 'Alasan pembatalan wajib diisi.']);

        expect($reservation->order->fresh()->status)->toBe(OrderStatus::PendingPayment);
    });

    it('refuses to cancel an order that is already closed', function () {
        $reservation = reservationWithOrder(['status' => OrderStatus::Expired]);
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('admin.reservations.cancel', $reservation), ['reason' => 'x'])
            ->assertSessionHasErrors(['reason' => 'Reservasi ini tidak dapat dibatalkan lagi.']);

        expect($reservation->order->fresh()->status)->toBe(OrderStatus::Expired);
    });
});
