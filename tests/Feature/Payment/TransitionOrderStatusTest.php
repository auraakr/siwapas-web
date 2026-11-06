<?php

use App\Domain\Booking\Models\Reservation;
use App\Domain\Payment\Actions\TransitionOrderStatus;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Exceptions\InvalidOrderTransition;
use App\Domain\Payment\Models\Order;
use App\Models\User;

it('moves the order and writes a status history row', function () {
    $order = Order::factory()->create();
    $admin = User::factory()->admin()->create();

    app(TransitionOrderStatus::class)->handle($order, OrderStatus::AwaitingVerification, $admin, 'checked');

    $history = $order->statusHistories()->sole();
    expect($order->fresh()->status)->toBe(OrderStatus::AwaitingVerification)
        ->and($history->from_status)->toBe(OrderStatus::PendingPayment)
        ->and($history->to_status)->toBe(OrderStatus::AwaitingVerification)
        ->and($history->actor_type)->toBe('admin')
        ->and($history->actor_id)->toBe($admin->id)
        ->and($history->note)->toBe('checked');
});

it('sets confirmed_at when the order is confirmed', function () {
    $order = Order::factory()->awaitingVerification()->create();

    app(TransitionOrderStatus::class)->handle($order, OrderStatus::Confirmed);

    expect($order->fresh()->confirmed_at)->not->toBeNull();
});

it('rejects a transition that the state machine does not allow', function (OrderStatus $from, OrderStatus $to) {
    $order = Order::factory()->create(['status' => $from]);

    expect(fn () => app(TransitionOrderStatus::class)->handle($order, $to))
        ->toThrow(InvalidOrderTransition::class);

    expect($order->fresh()->status)->toBe($from)
        ->and($order->statusHistories()->count())->toBe(0);
})->with([
    'expired orders cannot be revived' => [OrderStatus::Expired, OrderStatus::PendingPayment],
    'pending orders cannot skip verification' => [OrderStatus::PendingPayment, OrderStatus::Confirmed],
    'confirmed orders cannot become pending' => [OrderStatus::Confirmed, OrderStatus::PendingPayment],
    'rejected orders cannot be confirmed' => [OrderStatus::Rejected, OrderStatus::Confirmed],
]);

it('releases the reservation when the order stops holding inventory', function (OrderStatus $from, OrderStatus $to) {
    $order = Order::factory()->create(['status' => $from]);
    $reservation = Reservation::factory()->create(['order_id' => $order->id]);

    app(TransitionOrderStatus::class)->handle($order, $to);

    expect($reservation->fresh()->is_active)->toBeFalse();
})->with([
    'expired' => [OrderStatus::PendingPayment, OrderStatus::Expired],
    'cancelled' => [OrderStatus::PendingPayment, OrderStatus::Cancelled],
    'rejected' => [OrderStatus::AwaitingVerification, OrderStatus::Rejected],
]);

it('keeps the reservation while the order still holds inventory', function () {
    $order = Order::factory()->awaitingVerification()->create();
    $reservation = Reservation::factory()->create(['order_id' => $order->id]);

    app(TransitionOrderStatus::class)->handle($order, OrderStatus::Confirmed);

    expect($reservation->fresh()->is_active)->toBeTrue();
});
