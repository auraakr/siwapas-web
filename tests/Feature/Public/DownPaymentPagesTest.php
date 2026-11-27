<?php

use App\Domain\Booking\Actions\CreateReservation;
use App\Domain\Booking\DTOs\ReservationData;
use App\Domain\Booking\Exceptions\BookingTooSoon;
use App\Domain\Booking\Models\Reservation;
use App\Domain\Catalog\Models\Facility;
use App\Domain\Payment\Actions\RejectPayment;
use App\Domain\Payment\DTOs\CustomerData;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Models\BankAccount;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    BankAccount::factory()->create();
});

function bookFacilityAt(string $now, string $checkIn, string $checkOut): Order
{
    test()->travelTo($now);
    $facility = Facility::factory()->create(['base_price' => 200000, 'capacity' => 10]);

    return app(CreateReservation::class)->handle(new ReservationData(
        $facility->id,
        Date::parse($checkIn),
        Date::parse($checkOut),
        2,
        new CustomerData('Budi', '081234567890'),
    ));
}

describe('down payment deadline', function () {
    it('gives 24 hours when check-in is far away', function () {
        $order = bookFacilityAt('2026-11-15 03:00:00', '2026-12-01', '2026-12-02');

        expect($order->expires_at->toDateTimeString())->toBe('2026-11-16 03:00:00');
    });

    it('ends at the start of the check-in day in Jakarta when that comes sooner', function () {
        // 10:00 WIB on 15 Nov; check-in on 16 Nov starts at 00:00 WIB = 17:00 UTC on 15 Nov.
        $order = bookFacilityAt('2026-11-15 03:00:00', '2026-11-16', '2026-11-17');

        expect($order->expires_at->toDateTimeString())->toBe('2026-11-15 17:00:00');
    });

    it('still accepts a booking that leaves exactly the minimum two hours to pay', function () {
        $order = bookFacilityAt('2026-11-15 15:00:00', '2026-11-16', '2026-11-17');

        expect($order->expires_at->toDateTimeString())->toBe('2026-11-15 17:00:00');
    });

    it('refuses a booking that leaves less than two hours to pay', function (string $now, string $checkIn) {
        bookFacilityAt($now, $checkIn, '2026-11-20');
    })->throws(BookingTooSoon::class)->with([
        'one hour before the check-in day' => ['2026-11-15 16:00:00', '2026-11-16'],
        'check-in today' => ['2026-11-15 03:00:00', '2026-11-15'],
    ]);

    it('tells the visitor to pick another date or contact the managers on their WhatsApp number', function () {
        config(['siwapas.contact.whatsapp' => '081211112222']);
        $this->travelTo('2026-11-15 16:30:00');
        $facility = Facility::factory()->create(['capacity' => 10]);

        $this->post(route('reservations.store', $facility), [
            'check_in' => '2026-11-16',
            'check_out' => '2026-11-17',
            'guests' => 2,
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
        ])->assertSessionHasErrors(['check_in' => 'Tanggal check-in terlalu dekat sehingga waktu untuk membayar DP secara online tidak cukup. Silakan pilih tanggal lain, atau hubungi pengelola via WhatsApp 081211112222 untuk sewa mendadak.']);

        expect(Order::count())->toBe(0)->and(Reservation::count())->toBe(0);
    });
});

describe('booking form price preview', function () {
    it('quotes the full price, the down payment due now and the rest', function () {
        $this->travelTo('2026-11-15 03:00:00');
        $facility = Facility::factory()->create(['base_price' => 350001, 'capacity' => 10]);

        $this->get(route('facilities.show', $facility).'?check_in=2026-12-01&check_out=2026-12-03')
            ->assertInertia(fn (Assert $page) => $page
                ->where('dp_percentage', 50)
                ->missing('quote')
                ->reloadOnly('quote', fn (Assert $reload) => $reload
                    ->where('quote.nights', 2)
                    ->where('quote.total', 700002)
                    ->where('quote.dp_amount', 350001)
                    ->where('quote.remaining_amount', 350001)
                    ->where('quote.available', true)
                    ->where('quote.too_soon', false)));
    });

    it('warns before submitting when the dates are taken or too close', function () {
        $this->travelTo('2026-11-15 16:30:00');
        $facility = Facility::factory()->create(['capacity' => 10]);
        Reservation::factory()->create(['facility_id' => $facility->id, 'check_in' => '2026-11-16', 'check_out' => '2026-11-18']);

        $this->get(route('facilities.show', $facility).'?check_in=2026-11-16&check_out=2026-11-17')
            ->assertInertia(fn (Assert $page) => $page->reloadOnly('quote', fn (Assert $reload) => $reload
                ->where('quote.available', false)
                ->where('quote.too_soon', true)));
    });

    it('gives no quote for incomplete or reversed dates', function () {
        $facility = Facility::factory()->create();

        $this->get(route('facilities.show', $facility).'?check_in=2026-12-03&check_out=2026-12-01')
            ->assertInertia(fn (Assert $page) => $page->reloadOnly('quote', fn (Assert $reload) => $reload->where('quote', null)));
    });
});

describe('order page', function () {
    it('shows the down payment with its unique code and the rest still due', function () {
        $order = Order::factory()->withDownPayment()->create(['subtotal' => 500000, 'unique_code' => 123, 'total' => 500123]);
        Payment::factory()->downPayment()->for($order)->create();

        $this->get(route('orders.show', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->where('order.payment_plan', 'down_payment')
                ->where('order.dp_percentage', 50)
                ->where('order.dp_amount', 250000)
                ->where('order.first_payment_amount', 250123)
                ->where('order.remaining_amount', 250000)
                ->where('order.can_upload_proof', true)
                ->where('order.can_upload_settlement', false));
    });

    it('shows why a proof was rejected while the visitor can upload again', function () {
        $order = Order::factory()->withDownPayment()->awaitingVerification()->create();
        $payment = Payment::factory()->downPayment()->for($order)->create();
        app(RejectPayment::class)->handle($payment, User::factory()->admin()->create(), 'Foto bukti buram');

        $this->get(route('orders.show', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->where('order.status', 'pending_payment')
                ->where('order.rejection_reason', 'Foto bukti buram')
                ->where('order.can_upload_proof', true));
    });

    it('keeps event tickets on full payment', function () {
        $order = Order::factory()->create(['kind' => 'event_registration']);
        Payment::factory()->for($order)->create();

        $this->get(route('orders.show', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->where('order.payment_plan', 'full')
                ->where('order.first_payment_amount', 500123)
                ->where('order.remaining_amount', null));
    });
});

describe('settlement upload', function () {
    function dpPaidStay(string $checkOut): Order
    {
        $order = Order::factory()->dpPaid()->create();
        Reservation::factory()->create(['order_id' => $order->id, 'check_in' => '2026-11-14', 'check_out' => $checkOut]);
        Payment::factory()->downPayment()->verified()->for($order)->create();

        return $order;
    }

    it('accepts the settlement proof up to the end of the check-out day in Jakarta', function () {
        Storage::fake('local');
        // 23:30 WIB on the check-out day.
        $this->travelTo('2026-11-16 16:30:00');
        $order = dpPaidStay('2026-11-16');

        $this->get(route('orders.show', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->where('order.can_upload_settlement', true)
                ->where('order.settlement_closed', false));

        $this->post(route('orders.proof.store', $order), ['proof' => UploadedFile::fake()->image('lunas.jpg')])
            ->assertRedirect(route('orders.show', $order));

        expect($order->fresh()->status)->toBe(OrderStatus::AwaitingSettlementVerification);
    });

    it('closes the settlement upload after the check-out day and points to the managers', function () {
        Storage::fake('local');
        // 00:30 WIB the day after check-out.
        $this->travelTo('2026-11-16 17:30:00');
        $order = dpPaidStay('2026-11-16');

        $this->get(route('orders.show', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->where('order.can_upload_settlement', false)
                ->where('order.settlement_closed', true));

        $this->post(route('orders.proof.store', $order), ['proof' => UploadedFile::fake()->image('lunas.jpg')])
            ->assertSessionHasErrors('proof');

        expect($order->fresh()->status)->toBe(OrderStatus::DpPaid)
            ->and(Storage::disk('local')->allFiles())->toBeEmpty();
    });
});
