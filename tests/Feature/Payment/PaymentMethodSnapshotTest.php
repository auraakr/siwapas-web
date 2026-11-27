<?php

use App\Domain\Booking\Actions\CreateReservation;
use App\Domain\Booking\DTOs\ReservationData;
use App\Domain\Catalog\Models\Facility;
use App\Domain\Payment\DTOs\CustomerData;
use App\Domain\Payment\Models\BankAccount;
use Illuminate\Support\Facades\Date;

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
