<?php

use App\Domain\Booking\Actions\CreateReservation;
use App\Domain\Booking\DTOs\ReservationData;
use App\Domain\Catalog\Models\Facility;
use App\Domain\Payment\DTOs\CustomerData;
use App\Domain\Payment\Enums\PaymentMethodType;
use App\Domain\Payment\Models\BankAccount;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests and forbids registered visitors who are not admins', function () {
    $method = BankAccount::factory()->create();

    $this->get(route('admin.payment-methods.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create());
    $this->get(route('admin.payment-methods.index'))->assertForbidden();
    $this->post(route('admin.payment-methods.store'), ['type' => 'bank', 'name' => 'BCA'])->assertForbidden();
    $this->patch(route('admin.payment-methods.toggle', $method))->assertForbidden();
    $this->delete(route('admin.payment-methods.destroy', $method))->assertForbidden();

    expect($method->fresh()->is_active)->toBeTrue();
});

it('lists bank accounts and QRIS codes for the admin', function () {
    BankAccount::factory()->create(['bank_name' => 'BCA']);
    BankAccount::factory()->qris()->create();
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.payment-methods.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/payment-methods/index')
            ->has('methods', 2)
            ->where('methods.0.type', 'bank')
            ->where('methods.1.type', 'qris'));
});

it('adds a bank account', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.payment-methods.store'), [
        'type' => 'bank',
        'name' => 'BRI',
        'account_number' => '9876543210',
        'account_holder' => 'Desa Wisata',
    ])->assertRedirect();

    $method = BankAccount::sole();
    expect($method->type)->toBe(PaymentMethodType::Bank)
        ->and($method->bank_name)->toBe('BRI')
        ->and($method->account_number)->toBe('9876543210')
        ->and($method->is_active)->toBeTrue();
});

it('adds a QRIS code with its image', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.payment-methods.store'), [
        'type' => 'qris',
        'name' => 'QRIS Desa',
        'qris' => UploadedFile::fake()->image('qris.png'),
    ])->assertRedirect();

    $method = BankAccount::sole();
    expect($method->type)->toBe(PaymentMethodType::Qris)
        ->and($method->account_number)->toBeNull()
        ->and($method->getFirstMedia(BankAccount::QRIS_COLLECTION))->not->toBeNull()
        ->and($method->snapshot()['qris_url'])->not->toBeNull();
});

it('validates the payment method form', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.payment-methods.store'), [])->assertSessionHasErrors(['type', 'name']);
    $this->post(route('admin.payment-methods.store'), ['type' => 'bank', 'name' => 'BCA'])
        ->assertSessionHasErrors(['account_number' => 'Nomor rekening wajib diisi.', 'account_holder' => 'Nama pemilik rekening wajib diisi.']);
    $this->post(route('admin.payment-methods.store'), ['type' => 'qris', 'name' => 'QRIS'])
        ->assertSessionHasErrors(['qris' => 'Gambar QRIS wajib diunggah.']);
    $this->post(route('admin.payment-methods.store'), ['type' => 'qris', 'name' => 'QRIS', 'qris' => UploadedFile::fake()->create('qris.pdf', 10, 'application/pdf')])
        ->assertSessionHasErrors(['qris' => 'Gambar QRIS harus berupa JPG, PNG, atau WEBP.']);

    expect(BankAccount::count())->toBe(0);
});

it('toggles and deletes a method', function () {
    $method = BankAccount::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    $this->patch(route('admin.payment-methods.toggle', $method))->assertRedirect();
    expect($method->fresh()->is_active)->toBeFalse();

    $this->patch(route('admin.payment-methods.toggle', $method));
    expect($method->fresh()->is_active)->toBeTrue();

    $this->delete(route('admin.payment-methods.destroy', $method))->assertRedirect();
    expect(BankAccount::count())->toBe(0);
});

it('snapshots every active method on a new order and skips inactive ones', function () {
    $this->travelTo('2026-11-15 03:00:00');
    BankAccount::factory()->create(['bank_name' => 'BCA', 'sort_order' => 1]);
    BankAccount::factory()->qris()->create(['sort_order' => 2]);
    BankAccount::factory()->create(['bank_name' => 'BNI', 'is_active' => false]);
    $facility = Facility::factory()->create(['capacity' => 10]);

    $order = app(CreateReservation::class)->handle(new ReservationData(
        $facility->id,
        Date::parse('2030-01-07'),
        Date::parse('2030-01-08'),
        2,
        new CustomerData('Budi', '081234567890'),
    ));

    expect(collect($order->payments()->sole()->bank_snapshot)->pluck('name')->all())->toBe(['BCA', 'QRIS']);
});

it('lets a visitor order when only QRIS is configured', function () {
    $this->travelTo('2026-11-15 03:00:00');
    BankAccount::factory()->qris()->create();
    $facility = Facility::factory()->create(['capacity' => 10]);

    $order = app(CreateReservation::class)->handle(new ReservationData(
        $facility->id,
        Date::parse('2030-01-07'),
        Date::parse('2030-01-08'),
        2,
        new CustomerData('Budi', '081234567890'),
    ));

    expect($order->payments()->sole()->bank_snapshot[0]['type'])->toBe('qris');
});
