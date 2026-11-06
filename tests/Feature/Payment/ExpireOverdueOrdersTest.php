<?php

use App\Domain\Booking\Models\Reservation;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Models\Order;

it('expires unpaid orders past their deadline and frees their slot', function () {
    $order = Order::factory()->create(['expires_at' => now()->subHour()]);
    $reservation = Reservation::factory()->create(['order_id' => $order->id]);

    $this->artisan('orders:expire')->expectsOutput('Expired 1 order(s).')->assertSuccessful();

    expect($order->fresh()->status)->toBe(OrderStatus::Expired)
        ->and($reservation->fresh()->is_active)->toBeFalse()
        ->and($order->statusHistories()->sole()->to_status)->toBe(OrderStatus::Expired);
});

it('leaves orders that are within their deadline or already awaiting verification', function () {
    $fresh = Order::factory()->create();
    $awaiting = Order::factory()->awaitingVerification()->create(['expires_at' => now()->subHour()]);

    $this->artisan('orders:expire')->expectsOutput('Expired 0 order(s).')->assertSuccessful();

    expect($fresh->fresh()->status)->toBe(OrderStatus::PendingPayment)
        ->and($awaiting->fresh()->status)->toBe(OrderStatus::AwaitingVerification);
});

it('does nothing the second time it runs', function () {
    Order::factory()->create(['expires_at' => now()->subHour()]);

    $this->artisan('orders:expire')->expectsOutput('Expired 1 order(s).');
    $this->artisan('orders:expire')->expectsOutput('Expired 0 order(s).');
});
