<?php

use App\Domain\Booking\Models\Reservation;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests to the login page', function () {
    [, $payment] = orderAwaitingVerification();

    $this->get(route('admin.payments.index'))->assertRedirect(route('login'));
    $this->post(route('admin.payments.verify', $payment))->assertRedirect(route('login'));
    $this->post(route('admin.payments.reject', $payment), ['reason' => 'x'])->assertRedirect(route('login'));
});

it('forbids registered visitors who are not admins', function () {
    [, $payment] = orderAwaitingVerification();
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.payments.index'))->assertForbidden();
    $this->post(route('admin.payments.verify', $payment))->assertForbidden();
    $this->post(route('admin.payments.reject', $payment), ['reason' => 'x'])->assertForbidden();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending);
});

it('lists payments that wait for verification with short-lived proof links', function () {
    orderAwaitingVerification();
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.payments.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/payments/index')
            ->has('payments', 1)
            ->has('payments.0.proofs', 1)
            ->where('payments.0.proofs.0.url', fn (string $url) => str_contains($url, 'signature=') && str_contains($url, 'expires=')));
});

it('confirms the order and records who verified the payment', function () {
    [$order, $payment] = orderAwaitingVerification();
    $reservation = Reservation::factory()->create(['order_id' => $order->id]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.payments.verify', $payment))->assertRedirect();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Verified)
        ->and($payment->fresh()->verified_by)->toBe($admin->id)
        ->and($payment->fresh()->verified_at)->not->toBeNull()
        ->and($order->fresh()->status)->toBe(OrderStatus::Confirmed)
        ->and($reservation->fresh()->is_active)->toBeTrue();
});

it('does not verify a payment twice', function () {
    [, $payment] = orderAwaitingVerification();
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.payments.verify', $payment));
    $this->post(route('admin.payments.verify', $payment))
        ->assertSessionHasErrors(['payment' => 'Pembayaran ini sudah diproses.']);
});

it('rejects the payment with a reason and releases the slot', function () {
    [$order, $payment] = orderAwaitingVerification();
    $reservation = Reservation::factory()->create(['order_id' => $order->id]);
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.payments.reject', $payment), ['reason' => 'Nominal tidak sesuai'])->assertRedirect();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Rejected)
        ->and($payment->fresh()->rejection_reason)->toBe('Nominal tidak sesuai')
        ->and($order->fresh()->status)->toBe(OrderStatus::Rejected)
        ->and($reservation->fresh()->is_active)->toBeFalse();
});

it('requires a reason to reject a payment', function () {
    [$order, $payment] = orderAwaitingVerification();
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.payments.reject', $payment), [])->assertSessionHasErrors('reason');

    expect($order->fresh()->status)->toBe(OrderStatus::AwaitingVerification);
});

describe('proof files', function () {
    it('serves the file through a valid signed link to an admin', function () {
        Storage::fake('local');
        [, , $proof] = orderAwaitingVerification();
        Storage::disk('local')->put($proof->path, 'proof-bytes');
        $url = URL::temporarySignedRoute('admin.proofs.show', now()->addMinutes(5), ['proof' => $proof->id]);

        $this->actingAs(User::factory()->admin()->create())->get($url)->assertOk();
    });

    it('refuses a link without a valid signature', function () {
        Storage::fake('local');
        [, , $proof] = orderAwaitingVerification();
        Storage::disk('local')->put($proof->path, 'proof-bytes');

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.proofs.show', $proof))
            ->assertForbidden();
    });

    it('refuses an expired link', function () {
        Storage::fake('local');
        [, , $proof] = orderAwaitingVerification();
        $url = URL::temporarySignedRoute('admin.proofs.show', now()->addMinutes(5), ['proof' => $proof->id]);

        $this->travel(6)->minutes();

        $this->actingAs(User::factory()->admin()->create())->get($url)->assertForbidden();
    });

    it('refuses a valid link to a non-admin', function () {
        Storage::fake('local');
        [, , $proof] = orderAwaitingVerification();
        $url = URL::temporarySignedRoute('admin.proofs.show', now()->addMinutes(5), ['proof' => $proof->id]);

        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
    });
});
