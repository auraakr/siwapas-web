<?php

use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('stores the proof privately and moves the order to awaiting verification', function () {
    Storage::fake('local');
    $order = Order::factory()->create();
    $payment = Payment::factory()->for($order)->create();

    $this->post(route('orders.proof.store', $order), [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
        'sender_name' => 'Budi',
        'claimed_amount' => 500123,
    ])->assertRedirect(route('orders.show', $order));

    $proof = $payment->proofs()->sole();
    Storage::disk('local')->assertExists($proof->path);
    expect($proof->original_name)->toBe('bukti.jpg')
        ->and($proof->mime_type)->toBe('image/jpeg')
        ->and($proof->sender_name)->toBe('Budi')
        ->and($proof->claimed_amount)->toBe(500123)
        ->and($order->fresh()->status)->toBe(OrderStatus::AwaitingVerification);
});

it('rejects a proof that is not an image or PDF', function () {
    Storage::fake('local');
    $order = Order::factory()->create();
    Payment::factory()->for($order)->create();

    $this->post(route('orders.proof.store', $order), [
        'proof' => UploadedFile::fake()->create('bukti.docx', 100, 'application/msword'),
    ])->assertSessionHasErrors(['proof' => 'Bukti transfer harus berupa JPG, PNG, atau PDF.']);

    expect($order->fresh()->status)->toBe(OrderStatus::PendingPayment);
});

it('rejects a proof larger than 5 MB', function () {
    Storage::fake('local');
    $order = Order::factory()->create();
    Payment::factory()->for($order)->create();

    $this->post(route('orders.proof.store', $order), [
        'proof' => UploadedFile::fake()->create('bukti.pdf', 5121, 'application/pdf'),
    ])->assertSessionHasErrors(['proof' => 'Ukuran bukti transfer maksimal 5 MB.']);

    expect($order->fresh()->status)->toBe(OrderStatus::PendingPayment);
});

it('requires a proof file', function () {
    $order = Order::factory()->create();

    $this->post(route('orders.proof.store', $order), [])
        ->assertSessionHasErrors(['proof' => 'Bukti transfer wajib diunggah.']);
});

it('refuses a proof after the payment deadline', function () {
    Storage::fake('local');
    $order = Order::factory()->create(['expires_at' => now()->subMinute()]);
    Payment::factory()->for($order)->create();

    $this->post(route('orders.proof.store', $order), ['proof' => UploadedFile::fake()->image('bukti.png')])
        ->assertSessionHasErrors(['proof' => 'Pesanan ini tidak dapat menerima bukti transfer lagi.']);

    expect($order->fresh()->status)->toBe(OrderStatus::PendingPayment)
        ->and(Storage::disk('local')->allFiles())->toBeEmpty();
});

it('refuses a second proof while the first is being verified', function () {
    Storage::fake('local');
    [$order] = orderAwaitingVerification();

    $this->post(route('orders.proof.store', $order), ['proof' => UploadedFile::fake()->image('lagi.png')])
        ->assertSessionHasErrors('proof');
});

it('returns 404 for an unknown access token', function () {
    $this->post('/pesanan/'.str_repeat('x', 48).'/bukti', [])->assertNotFound();
});
