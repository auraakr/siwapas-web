<?php

use App\Domain\Booking\Actions\CreateReservation;
use App\Domain\Booking\DTOs\ReservationData;
use App\Domain\Booking\Exceptions\FacilityUnavailable;
use App\Domain\Booking\Models\Reservation;
use App\Domain\Catalog\Enums\PricingRuleType;
use App\Domain\Catalog\Models\BlockedDate;
use App\Domain\Catalog\Models\Facility;
use App\Domain\Catalog\Models\PricingRule;
use App\Domain\Payment\DTOs\CustomerData;
use App\Domain\Payment\Enums\OrderKind;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Exceptions\PaymentNotPossible;
use App\Domain\Payment\Models\BankAccount;
use Illuminate\Support\Facades\Date;

function reservationData(Facility $facility, string $checkIn, string $checkOut, int $guests = 2): ReservationData
{
    return new ReservationData(
        facilityId: $facility->id,
        checkIn: Date::parse($checkIn),
        checkOut: Date::parse($checkOut),
        guests: $guests,
        customer: new CustomerData('Budi', '081234567890', 'budi@example.com'),
    );
}

beforeEach(function () {
    $this->travelTo('2026-11-15 03:00:00');
});

it('creates a pending order, an active reservation and a manual transfer payment', function () {
    $bank = BankAccount::factory()->create(['bank_name' => 'BCA', 'account_number' => '1234567890']);
    $facility = Facility::factory()->create(['base_price' => 500000, 'capacity' => 10]);

    $order = app(CreateReservation::class)->handle(reservationData($facility, '2030-01-07', '2030-01-09'));

    expect($order->code)->toBe('SWP-2611-0001')
        ->and($order->kind)->toBe(OrderKind::Reservation)
        ->and($order->status)->toBe(OrderStatus::PendingPayment)
        ->and($order->subtotal)->toBe(1000000)
        ->and($order->unique_code)->toBeBetween(1, 999)
        ->and($order->total)->toBe(1000000 + $order->unique_code)
        ->and($order->access_token)->toHaveLength(48)
        ->and($order->expires_at->toDateTimeString())->toBe('2026-11-16 03:00:00');

    $reservation = $order->reservation;
    expect($reservation->facility_id)->toBe($facility->id)
        ->and($reservation->is_active)->toBeTrue()
        ->and($reservation->check_in->toDateString())->toBe('2030-01-07')
        ->and($reservation->check_out->toDateString())->toBe('2030-01-09');

    $payment = $order->payments()->sole();
    expect($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->expected_amount)->toBe($order->total)
        ->and($payment->bank_account_id)->toBe($bank->id)
        ->and($payment->bank_snapshot)->toBe([['type' => 'bank', 'name' => 'BCA', 'account_number' => '1234567890', 'account_holder' => $bank->account_holder, 'qris_url' => null]]);

    expect($order->statusHistories()->sole()->to_status)->toBe(OrderStatus::PendingPayment);
});

it('numbers order codes sequentially within the month', function () {
    BankAccount::factory()->create();
    $facility = Facility::factory()->create(['capacity' => 10]);

    $first = app(CreateReservation::class)->handle(reservationData($facility, '2030-01-07', '2030-01-08'));
    $second = app(CreateReservation::class)->handle(reservationData($facility, '2030-01-09', '2030-01-10'));

    expect($first->code)->toBe('SWP-2611-0001')
        ->and($second->code)->toBe('SWP-2611-0002');
});

it('prices each night with the highest priority matching rule', function () {
    BankAccount::factory()->create();
    $facility = Facility::factory()->create(['base_price' => 500000, 'capacity' => 10]);
    PricingRule::factory()->for($facility)->create([
        'type' => PricingRuleType::Weekend,
        'days_of_week' => null,
        'price' => 800000,
        'priority' => 1,
    ]);

    // 2030-01-11 is a Friday and 2030-01-12 a Saturday.
    $order = app(CreateReservation::class)->handle(reservationData($facility, '2030-01-11', '2030-01-13'));

    expect($order->subtotal)->toBe(500000 + 800000)
        ->and($order->reservation->price_breakdown)->toHaveCount(2)
        ->and($order->reservation->price_breakdown[1])->toMatchArray(['date' => '2030-01-12', 'price' => 800000]);
});

it('rejects dates that overlap an active reservation', function () {
    BankAccount::factory()->create();
    $facility = Facility::factory()->create(['capacity' => 10]);
    Reservation::factory()->create(['facility_id' => $facility->id, 'check_in' => '2030-01-10', 'check_out' => '2030-01-13']);

    expect(fn () => app(CreateReservation::class)->handle(reservationData($facility, '2030-01-12', '2030-01-14')))
        ->toThrow(FacilityUnavailable::class);
});

it('allows back-to-back stays and dates held only by an inactive reservation', function () {
    BankAccount::factory()->create();
    $facility = Facility::factory()->create(['capacity' => 10]);
    Reservation::factory()->create(['facility_id' => $facility->id, 'check_in' => '2030-01-10', 'check_out' => '2030-01-13']);
    Reservation::factory()->inactive()->create(['facility_id' => $facility->id, 'check_in' => '2030-01-20', 'check_out' => '2030-01-23']);

    $backToBack = app(CreateReservation::class)->handle(reservationData($facility, '2030-01-13', '2030-01-15'));
    $overInactive = app(CreateReservation::class)->handle(reservationData($facility, '2030-01-21', '2030-01-22'));

    expect($backToBack->reservation->exists)->toBeTrue()
        ->and($overInactive->reservation->exists)->toBeTrue();
});

it('rejects dates inside a blocked range', function () {
    BankAccount::factory()->create();
    $facility = Facility::factory()->create(['capacity' => 10]);
    BlockedDate::factory()->create(['facility_id' => $facility->id, 'starts_on' => '2030-02-10', 'ends_on' => '2030-02-12']);

    expect(fn () => app(CreateReservation::class)->handle(reservationData($facility, '2030-02-12', '2030-02-14')))
        ->toThrow(FacilityUnavailable::class);
});

it('rejects an inactive facility', function () {
    BankAccount::factory()->create();
    $facility = Facility::factory()->create(['is_active' => false, 'capacity' => 10]);

    expect(fn () => app(CreateReservation::class)->handle(reservationData($facility, '2030-01-07', '2030-01-08')))
        ->toThrow(FacilityUnavailable::class);
});

it('rejects a check-in date in the past', function () {
    BankAccount::factory()->create();
    $facility = Facility::factory()->create(['capacity' => 10]);

    expect(fn () => app(CreateReservation::class)->handle(reservationData($facility, '2026-11-14', '2026-11-16')))
        ->toThrow(FacilityUnavailable::class);
});

it('accepts a check-in today in Jakarta even when it is still yesterday in UTC', function () {
    $this->travelTo('2026-11-14 20:00:00'); // 03:00 on 2026-11-15 in Jakarta
    BankAccount::factory()->create();
    $facility = Facility::factory()->create(['capacity' => 10]);

    $order = app(CreateReservation::class)->handle(reservationData($facility, '2026-11-15', '2026-11-16'));

    expect($order->reservation->check_in->toDateString())->toBe('2026-11-15');
});

it('rejects more guests than the facility capacity', function () {
    BankAccount::factory()->create();
    $facility = Facility::factory()->create(['capacity' => 5]);

    expect(fn () => app(CreateReservation::class)->handle(reservationData($facility, '2030-01-07', '2030-01-08', guests: 6)))
        ->toThrow(FacilityUnavailable::class);
});

it('creates nothing when no bank account is configured', function () {
    $facility = Facility::factory()->create(['capacity' => 10]);

    expect(fn () => app(CreateReservation::class)->handle(reservationData($facility, '2030-01-07', '2030-01-08')))
        ->toThrow(PaymentNotPossible::class);

    expect(Reservation::count())->toBe(0);
});
