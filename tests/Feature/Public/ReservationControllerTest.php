<?php

use App\Domain\Booking\Models\Reservation;
use App\Domain\Catalog\Models\Facility;
use App\Domain\Payment\Models\BankAccount;
use App\Domain\Payment\Models\Order;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array<string, mixed>
 */
function reservationPayload(array $overrides = []): array
{
    return [
        'check_in' => '2030-01-07',
        'check_out' => '2030-01-09',
        'guests' => 2,
        'customer_name' => 'Budi Santoso',
        'customer_phone' => '081234567890',
        'customer_email' => 'budi@example.com',
        ...$overrides,
    ];
}

it('lists only active facilities', function () {
    Facility::factory()->create(['slug' => 'aula', 'is_active' => true]);
    Facility::factory()->create(['slug' => 'gudang', 'is_active' => false]);

    $this->get(route('facilities.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/facilities/index')
            ->has('facilities', 1)
            ->where('facilities.0.slug', 'aula'));
});

it('shows a facility with the ranges that are already taken', function () {
    $facility = Facility::factory()->create();
    Reservation::factory()->create(['facility_id' => $facility->id, 'check_in' => '2030-01-10', 'check_out' => '2030-01-13']);
    Reservation::factory()->inactive()->create(['facility_id' => $facility->id, 'check_in' => '2030-02-10', 'check_out' => '2030-02-13']);

    $this->get(route('facilities.show', $facility))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/facilities/show')
            ->has('unavailable', 1)
            ->where('unavailable.0.from', '2030-01-10')
            ->where('unavailable.0.to', '2030-01-13'));
});

it('returns 404 for an inactive facility', function () {
    $facility = Facility::factory()->create(['is_active' => false]);

    $this->get(route('facilities.show', $facility))->assertNotFound();
    $this->post(route('reservations.store', $facility), reservationPayload())->assertNotFound();
});

it('creates the order and redirects the visitor to the private order page', function () {
    BankAccount::factory()->create();
    $facility = Facility::factory()->create(['capacity' => 10, 'base_price' => 500000]);

    $response = $this->post(route('reservations.store', $facility), reservationPayload());

    $order = Order::sole();
    $response->assertRedirect(route('orders.show', $order));
    expect($order->customer_name)->toBe('Budi Santoso')
        ->and($order->reservation->facility_id)->toBe($facility->id);
});

it('validates the reservation form', function () {
    $facility = Facility::factory()->create();

    $this->post(route('reservations.store', $facility), [])
        ->assertSessionHasErrors(['check_in', 'check_out', 'guests', 'customer_name', 'customer_phone']);

    $this->post(route('reservations.store', $facility), reservationPayload(['check_out' => '2030-01-07']))
        ->assertSessionHasErrors(['check_out' => 'Tanggal check-out harus setelah tanggal check-in.']);

    $this->post(route('reservations.store', $facility), reservationPayload(['customer_phone' => '12345']))
        ->assertSessionHasErrors(['customer_phone' => 'Nomor telepon tidak valid. Gunakan format 08xxxxxxxxxx.']);

    expect(Order::count())->toBe(0);
});

it('reports unavailable dates as a form error', function () {
    BankAccount::factory()->create();
    $facility = Facility::factory()->create(['capacity' => 10]);
    Reservation::factory()->create(['facility_id' => $facility->id, 'check_in' => '2030-01-06', 'check_out' => '2030-01-10']);

    $this->post(route('reservations.store', $facility), reservationPayload())
        ->assertSessionHasErrors(['check_in' => 'Fasilitas tidak tersedia pada tanggal tersebut atau data reservasi tidak valid.']);

    expect(Order::count())->toBe(1); // only the order of the existing reservation
});

it('rate limits repeated submissions from one address', function () {
    $facility = Facility::factory()->create();

    foreach (range(1, 10) as $attempt) {
        $this->post(route('reservations.store', $facility), [])->assertSessionHasErrors();
    }

    $this->post(route('reservations.store', $facility), [])->assertTooManyRequests();
});
