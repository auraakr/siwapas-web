<?php

use App\Domain\Booking\Models\Reservation;
use App\Domain\Event\Models\Event;
use App\Domain\Event\Models\EventRegistration;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Filament\Widgets\OperationsOverview;
use App\Filament\Widgets\PaymentsAwaitingVerification;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

it('counts the work that is waiting for the admin', function () {
    orderAwaitingVerification();
    orderAwaitingVerification();
    Order::factory()->create();
    Reservation::factory()->create(['order_id' => Order::factory()->create(['status' => OrderStatus::Confirmed])->id]);
    Reservation::factory()->create([
        'order_id' => Order::factory()->create(['status' => OrderStatus::Confirmed])->id,
        'check_in' => '2020-01-01',
        'check_out' => '2020-01-03',
    ]);
    $event = Event::factory()->create();
    EventRegistration::factory()->create(['event_id' => $event->id, 'participant_count' => 3]);
    EventRegistration::factory()->create(['event_id' => $event->id, 'participant_count' => 9, 'is_active' => false]);

    Livewire::test(OperationsOverview::class)->assertSeeInOrder([
        'Menunggu verifikasi', '2',
        'Menunggu pembayaran', '1',
        'Reservasi mendatang', '1',
        'Peserta acara mendatang', '3',
    ]);
});

it('lists the payments that wait for verification', function () {
    [, $waiting] = orderAwaitingVerification();
    $unpaid = Payment::factory()->for(Order::factory()->create())->create();

    Livewire::test(PaymentsAwaitingVerification::class)
        ->assertCanSeeTableRecords([$waiting])
        ->assertCanNotSeeTableRecords([$unpaid]);
});

it('shows the widgets on the admin dashboard', function () {
    $this->get('/admin')
        ->assertOk()
        ->assertSee('Menunggu verifikasi')
        ->assertSee('Pembayaran menunggu verifikasi');
});

it('links to the public pages from the admin navigation', function () {
    $this->get('/admin')
        ->assertOk()
        ->assertSee('Situs Publik')
        ->assertSee('href="'.route('home').'"', false)
        ->assertSee('href="'.route('facilities.index').'"', false)
        ->assertSee('href="'.route('events.index').'"', false);
});
