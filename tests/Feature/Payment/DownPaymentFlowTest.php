<?php

use App\Domain\Booking\Actions\CreateReservation;
use App\Domain\Booking\DTOs\ReservationData;
use App\Domain\Booking\Models\Reservation;
use App\Domain\Catalog\Models\Facility;
use App\Domain\Event\Actions\RegisterForEvent;
use App\Domain\Event\DTOs\EventRegistrationData;
use App\Domain\Event\Models\Event;
use App\Domain\Payment\Actions\CancelOrder;
use App\Domain\Payment\Actions\ExpireOverdueOrders;
use App\Domain\Payment\Actions\MarkOrderPaidInFull;
use App\Domain\Payment\Actions\RejectPayment;
use App\Domain\Payment\Actions\SubmitPaymentProof;
use App\Domain\Payment\Actions\VerifyPayment;
use App\Domain\Payment\DTOs\CustomerData;
use App\Domain\Payment\Enums\CancellationParty;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Enums\PaymentType;
use App\Domain\Payment\Enums\SettlementMethod;
use App\Domain\Payment\Exceptions\InvalidOrderTransition;
use App\Domain\Payment\Exceptions\PaymentNotPossible;
use App\Domain\Payment\Models\BankAccount;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Services\DownPaymentCalculator;
use App\Domain\Payment\Services\PaymentSettings;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // 10:00 in Jakarta.
    $this->travelTo('2026-11-15 03:00:00');
    Storage::fake('local');
    BankAccount::factory()->create();
    $this->admin = User::factory()->admin()->create();
});

/**
 * A two-night stay at Rp 350.001 per night unless a price is given, checking in well ahead.
 */
function bookStay(int $pricePerNight = 350001, string $checkIn = '2026-12-01', string $checkOut = '2026-12-03'): Order
{
    $facility = Facility::factory()->create(['base_price' => $pricePerNight, 'capacity' => 10]);

    return app(CreateReservation::class)->handle(new ReservationData(
        $facility->id,
        Date::parse($checkIn),
        Date::parse($checkOut),
        2,
        new CustomerData('Budi', '081234567890'),
    ));
}

function uploadProof(Order $order): void
{
    app(SubmitPaymentProof::class)->handle($order->fresh(), UploadedFile::fake()->image('bukti.jpg'));
}

function pendingPayment(Order $order): Payment
{
    return $order->payments()->where('status', PaymentStatus::Pending)->latest('id')->firstOrFail();
}

describe('down payment amount', function () {
    it('rounds the down payment up to the next rupiah so both parts add up to the full price', function (int $price, int $percentage, int $downPayment, int $remaining) {
        expect(app(DownPaymentCalculator::class)->split($price, $percentage))
            ->toBe(['dp_amount' => $downPayment, 'remaining_amount' => $remaining]);
    })->with([
        'even price' => [1000000, 50, 500000, 500000],
        'odd price' => [700001, 50, 350001, 350000],
        'a fraction of a rupiah' => [99999, 30, 30000, 69999],
        'whole price' => [250000, 100, 250000, 0],
        'free' => [0, 50, 0, 0],
    ]);

    it('refuses a percentage outside 1 to 100', function (int $percentage) {
        app(DownPaymentCalculator::class)->split(100000, $percentage);
    })->throws(InvalidArgumentException::class)->with([0, 101]);

    it('charges the down payment plus the unique code now and keeps the rest for later', function () {
        $order = bookStay();

        expect($order->subtotal)->toBe(700002)
            ->and($order->dp_percentage)->toBe(50)
            ->and($order->dp_amount)->toBe(350001)
            ->and($order->remaining_amount)->toBe(350001)
            ->and($order->total)->toBe(700002 + $order->unique_code);

        $payment = $order->payments()->sole();
        expect($payment->type)->toBe(PaymentType::DownPayment)
            ->and($payment->expected_amount)->toBe(350001 + $order->unique_code);
    });

    it('snapshots the percentage from the payment settings when the order is made', function () {
        app(PaymentSettings::class)->setDownPaymentPercentage(30);
        $order = bookStay(100000);

        app(PaymentSettings::class)->setDownPaymentPercentage(70);

        expect($order->fresh()->dp_percentage)->toBe(30)
            ->and($order->fresh()->dp_amount)->toBe(60000)
            ->and($order->fresh()->remaining_amount)->toBe(140000);
    });

    it('falls back to 50 percent from config when nothing is stored', function () {
        expect(app(PaymentSettings::class)->downPaymentPercentage())->toBe(50);
    });
});

describe('paying a reservation', function () {
    it('goes from down payment to paid in full by transfer and records every step', function () {
        $order = bookStay();

        uploadProof($order);
        expect($order->fresh()->status)->toBe(OrderStatus::AwaitingVerification);

        app(VerifyPayment::class)->handle(pendingPayment($order), $this->admin);
        expect($order->fresh()->status)->toBe(OrderStatus::DpPaid)
            ->and($order->fresh()->confirmed_at)->not->toBeNull()
            ->and($order->reservation->fresh()->is_active)->toBeTrue();

        uploadProof($order);
        $settlement = pendingPayment($order);
        expect($order->fresh()->status)->toBe(OrderStatus::AwaitingSettlementVerification)
            ->and($settlement->type)->toBe(PaymentType::Settlement)
            ->and($settlement->expected_amount)->toBe(350001);

        app(VerifyPayment::class)->handle($settlement, $this->admin);

        $order->refresh();
        expect($order->status)->toBe(OrderStatus::PaidInFull)
            ->and($order->settlement_method)->toBe(SettlementMethod::Transfer)
            ->and($order->settled_at)->not->toBeNull()
            ->and($order->payments()->where('status', PaymentStatus::Verified)->pluck('type')->all())
            ->toEqualCanonicalizing([PaymentType::DownPayment, PaymentType::Settlement])
            ->and($order->statusHistories()->orderBy('id')->pluck('to_status')->all())->toBe([
                OrderStatus::PendingPayment,
                OrderStatus::AwaitingVerification,
                OrderStatus::DpPaid,
                OrderStatus::AwaitingSettlementVerification,
                OrderStatus::PaidInFull,
            ]);
    });

    it('lets the admin mark the rest as paid in cash on site', function () {
        $order = Order::factory()->dpPaid()->create();

        app(MarkOrderPaidInFull::class)->handle($order, $this->admin, SettlementMethod::Cash, 'Diterima Pak RT');

        $order->refresh();
        $cash = $order->latestPaymentOf(PaymentType::Settlement);
        expect($order->status)->toBe(OrderStatus::PaidInFull)
            ->and($order->settlement_method)->toBe(SettlementMethod::Cash)
            ->and($cash->method)->toBe('cash_on_site')
            ->and($cash->status)->toBe(PaymentStatus::Verified)
            ->and($cash->expected_amount)->toBe(250000)
            ->and($order->statusHistories()->latest('id')->first()->note)->toContain('Diterima Pak RT');
    });

    it('settles a waiting settlement proof when the admin marks the order paid by transfer', function () {
        $order = Order::factory()->withDownPayment()->create(['status' => OrderStatus::AwaitingSettlementVerification]);
        $settlement = Payment::factory()->settlement()->for($order)->create();

        app(MarkOrderPaidInFull::class)->handle($order, $this->admin, SettlementMethod::Transfer);

        expect($order->fresh()->status)->toBe(OrderStatus::PaidInFull)
            ->and($settlement->fresh()->status)->toBe(PaymentStatus::Verified)
            ->and($order->payments()->count())->toBe(1);
    });

    it('does not mark an order paid in full before the down payment is verified, nor an event ticket', function (Closure $order) {
        expect(fn () => app(MarkOrderPaidInFull::class)->handle($order(), $this->admin, SettlementMethod::Cash))
            ->toThrow(InvalidOrderTransition::class);
    })->with([
        'down payment not verified yet' => [fn () => Order::factory()->withDownPayment()->create()],
        'full payment order' => [fn () => Order::factory()->confirmed()->create()],
    ]);

    it('keeps the booking when a settlement proof is rejected so the visitor can send another', function () {
        $order = Order::factory()->withDownPayment()->create(['status' => OrderStatus::AwaitingSettlementVerification]);
        $reservation = Reservation::factory()->create(['order_id' => $order->id, 'check_out' => '2026-12-03']);
        $settlement = Payment::factory()->settlement()->for($order)->create();

        app(RejectPayment::class)->handle($settlement, $this->admin, 'Nominal kurang');

        expect($order->fresh()->status)->toBe(OrderStatus::DpPaid)
            ->and($reservation->fresh()->is_active)->toBeTrue()
            ->and($order->fresh()->acceptsSettlementProof())->toBeTrue();
    });
});

describe('rejected first payment', function () {
    it('sends the order back to pending payment with a fresh payment of the same amount', function () {
        $order = bookStay();
        uploadProof($order);
        $rejected = pendingPayment($order);

        app(RejectPayment::class)->handle($rejected, $this->admin, 'Bukti buram');

        $fresh = pendingPayment($order);
        expect($order->fresh()->status)->toBe(OrderStatus::PendingPayment)
            ->and($order->reservation->fresh()->is_active)->toBeTrue()
            ->and($rejected->fresh()->status)->toBe(PaymentStatus::Rejected)
            ->and($fresh->id)->not->toBe($rejected->id)
            ->and($fresh->type)->toBe(PaymentType::DownPayment)
            ->and($fresh->expected_amount)->toBe($rejected->expected_amount);

        uploadProof($order);
        expect($order->fresh()->status)->toBe(OrderStatus::AwaitingVerification)
            ->and($fresh->proofs()->count())->toBe(1);
    });

    it('expires the order and releases the slot when the deadline has passed', function () {
        $order = bookStay();
        uploadProof($order);

        $this->travelTo($order->expires_at->addMinute());
        app(RejectPayment::class)->handle(pendingPayment($order), $this->admin, 'Bukti buram');

        expect($order->fresh()->status)->toBe(OrderStatus::Expired)
            ->and($order->reservation->fresh()->is_active)->toBeFalse()
            ->and($order->payments()->where('status', PaymentStatus::Pending)->exists())->toBeFalse();
    });

    it('rejects permanently and releases the slot when asked to', function () {
        $order = bookStay();
        uploadProof($order);

        app(RejectPayment::class)->handle(pendingPayment($order), $this->admin, 'Bukti palsu', permanent: true);

        expect($order->fresh()->status)->toBe(OrderStatus::Rejected)
            ->and($order->reservation->fresh()->is_active)->toBeFalse();
    });

    it('treats a rejected event ticket payment the same way', function () {
        $order = Order::factory()->awaitingVerification()->create(['kind' => 'event_registration']);
        $payment = Payment::factory()->for($order)->create();

        app(RejectPayment::class)->handle($payment, $this->admin, 'Nominal tidak sesuai');

        expect($order->fresh()->status)->toBe(OrderStatus::PendingPayment)
            ->and(pendingPayment($order)->type)->toBe(PaymentType::Full);
    });

    it('does not decide the same payment twice', function () {
        $order = bookStay();
        uploadProof($order);
        $payment = pendingPayment($order);
        app(VerifyPayment::class)->handle($payment, $this->admin);

        expect(fn () => app(RejectPayment::class)->handle($payment, $this->admin, 'Terlambat'))
            ->toThrow(InvalidOrderTransition::class);
    });
});

describe('cancelling and expiring', function () {
    it('records a cancellation by the tenant, forfeits the down payment and releases the slot', function () {
        $order = Order::factory()->dpPaid()->create();
        $reservation = Reservation::factory()->create(['order_id' => $order->id]);
        $downPayment = Payment::factory()->downPayment()->verified()->for($order)->create();

        app(CancelOrder::class)->handle($order, $this->admin, null, CancellationParty::Tenant);

        $order->refresh();
        expect($order->status)->toBe(OrderStatus::Cancelled)
            ->and($order->cancelled_by)->toBe(CancellationParty::Tenant)
            ->and($reservation->fresh()->is_active)->toBeFalse()
            ->and($downPayment->fresh()->status)->toBe(PaymentStatus::Verified)
            ->and($order->statusHistories()->sole()->note)->toContain('forfeited');
    });

    it('records how a cancellation by the managers was settled', function () {
        $order = Order::factory()->dpPaid()->create();

        app(CancelOrder::class)->handle($order, $this->admin, 'DP dikembalikan penuh via transfer', CancellationParty::Manager);

        expect($order->fresh()->cancelled_by)->toBe(CancellationParty::Manager)
            ->and($order->fresh()->resolution_note)->toBe('DP dikembalikan penuh via transfer')
            ->and($order->statusHistories()->sole()->note)->toBe('DP dikembalikan penuh via transfer');
    });

    it('expires an unpaid down payment after its deadline and releases the slot', function () {
        $order = bookStay();

        $this->travelTo($order->expires_at->addMinute());
        app(ExpireOverdueOrders::class)->handle();

        expect($order->fresh()->status)->toBe(OrderStatus::Expired)
            ->and($order->reservation->fresh()->is_active)->toBeFalse();
    });

    it('never expires a booking whose down payment is paid', function () {
        $order = Order::factory()->dpPaid()->create(['expires_at' => now()->subDay()]);

        app(ExpireOverdueOrders::class)->handle();

        expect($order->fresh()->status)->toBe(OrderStatus::DpPaid);
    });
});

describe('event tickets', function () {
    it('are still paid in full and confirmed once verified', function () {
        $event = Event::factory()->create(['fee' => 25000]);

        $registration = app(RegisterForEvent::class)->handle(new EventRegistrationData(
            $event->id,
            new CustomerData('Siti', '081234567890'),
            [['name' => 'Siti'], ['name' => 'Ani']],
        ));
        $order = $registration->order;
        $payment = $order->payments()->sole();

        expect($order->requiresDownPayment())->toBeFalse()
            ->and($order->dp_percentage)->toBeNull()
            ->and($order->remaining_amount)->toBeNull()
            ->and($payment->type)->toBe(PaymentType::Full)
            ->and($payment->expected_amount)->toBe(50000 + $order->unique_code)
            ->and($order->expires_at->toDateTimeString())->toBe('2026-11-16 03:00:00');

        uploadProof($order);
        app(VerifyPayment::class)->handle(pendingPayment($order), $this->admin);

        expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);
    });

    it('do not accept a settlement proof once confirmed', function () {
        $order = Order::factory()->confirmed()->create(['kind' => 'event_registration']);

        expect(fn () => uploadProof($order))->toThrow(PaymentNotPossible::class);
    });
});
