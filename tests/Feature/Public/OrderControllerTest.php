<?php

use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\Payment;
use Inertia\Testing\AssertableInertia as Assert;

it('shows the order and bank details to whoever holds the private link', function () {
    $order = Order::factory()->create(['code' => 'SWP-2611-0007', 'total' => 500123]);
    Payment::factory()->for($order)->create(['bank_snapshot' => [
        ['type' => 'bank', 'name' => 'BCA', 'account_number' => '1234567890', 'account_holder' => 'Desa Wisata', 'qris_url' => null],
        ['type' => 'qris', 'name' => 'QRIS Desa', 'account_number' => null, 'account_holder' => null, 'qris_url' => 'https://example.test/qris.png'],
    ]]);

    $this->get(route('orders.show', $order))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/orders/show')
            ->where('order.code', 'SWP-2611-0007')
            ->where('order.status', 'pending_payment')
            ->where('order.total', 500123)
            ->where('order.can_upload_proof', true)
            ->has('payment_methods', 2)
            ->where('payment_methods.0.name', 'BCA')
            ->where('payment_methods.1.qris_url', 'https://example.test/qris.png'));
});

it('does not allow uploads once the deadline has passed', function () {
    $order = Order::factory()->create(['expires_at' => now()->subMinute()]);
    Payment::factory()->for($order)->create();

    $this->get(route('orders.show', $order))
        ->assertInertia(fn (Assert $page) => $page->where('order.can_upload_proof', false));
});

it('returns 404 for an unknown access token and does not accept the order code', function () {
    $order = Order::factory()->create();

    $this->get('/pesanan/'.str_repeat('x', 48))->assertNotFound();
    $this->get('/pesanan/'.$order->code)->assertNotFound();
});
