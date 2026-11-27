<?php

use App\Domain\Booking\Models\Reservation;
use App\Domain\Payment\Enums\CancellationParty;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Enums\SettlementMethod;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Services\PaymentSettings;
use App\Filament\Pages\PaymentSettingsPage;
use App\Filament\Resources\PaymentResource\Pages\ManagePayments;
use App\Filament\Resources\ReservationResource\Pages\ListReservations;
use App\Filament\Resources\ReservationResource\Pages\ViewReservation;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

function dpReservation(OrderStatus $status = OrderStatus::DpPaid): Reservation
{
    $order = Order::factory()->withDownPayment()->create([
        'status' => $status,
        'confirmed_at' => $status === OrderStatus::PendingPayment ? null : now(),
    ]);

    return Reservation::factory()->create(['order_id' => $order->id]);
}

it('shows the down payment, the rest and the settlement state of a reservation', function () {
    $reservation = dpReservation();

    Livewire::test(ListReservations::class)
        ->assertCanSeeTableRecords([$reservation])
        ->assertTableColumnStateSet('order.dp_amount', 250000, $reservation)
        ->assertTableColumnStateSet('order.remaining_amount', 250000, $reservation)
        ->assertTableColumnStateSet('settlement', 'Belum lunas', $reservation);

    Livewire::test(ViewReservation::class, ['record' => $reservation->getRouteKey()])
        ->assertSee('Sisa pelunasan')
        ->assertSee('DP 50%');
});

it('marks a reservation paid in full in cash on site, also after the check-out day', function () {
    $this->travelTo('2026-11-20 05:00:00');
    $reservation = dpReservation();
    $reservation->update(['check_in' => '2026-11-10', 'check_out' => '2026-11-11']);

    Livewire::test(ListReservations::class)
        ->assertTableActionVisible('markPaidInFull', $reservation)
        ->callTableAction('markPaidInFull', $reservation, ['method' => 'cash', 'note' => 'Dibayar saat check-in'])
        ->assertHasNoTableActionErrors();

    $order = $reservation->order->fresh();
    expect($order->status)->toBe(OrderStatus::PaidInFull)
        ->and($order->settlement_method)->toBe(SettlementMethod::Cash);
});

it('offers marking paid in full only once the down payment is verified', function () {
    $pending = dpReservation(OrderStatus::PendingPayment);
    $paid = dpReservation(OrderStatus::PaidInFull);

    Livewire::test(ListReservations::class)
        ->assertTableActionHidden('markPaidInFull', $pending)
        ->assertTableActionHidden('markPaidInFull', $paid);
});

it('requires a settlement note when the managers cancel', function () {
    $reservation = dpReservation();

    Livewire::test(ListReservations::class)
        ->callTableAction('cancel', $reservation, ['party' => 'manager', 'note' => ''])
        ->assertHasTableActionErrors(['note' => 'required']);

    expect($reservation->order->fresh()->status)->toBe(OrderStatus::DpPaid);

    Livewire::test(ListReservations::class)
        ->callTableAction('cancel', $reservation, ['party' => 'manager', 'note' => 'DP dikembalikan'])
        ->assertHasNoTableActionErrors();

    expect($reservation->order->fresh()->cancelled_by)->toBe(CancellationParty::Manager)
        ->and($reservation->order->fresh()->resolution_note)->toBe('DP dikembalikan');
});

it('lets the admin verify a settlement proof from the payments list', function () {
    $order = Order::factory()->withDownPayment()->create(['status' => OrderStatus::AwaitingSettlementVerification]);
    $settlement = Payment::factory()->settlement()->for($order)->create();

    Livewire::test(ManagePayments::class)
        ->assertTableColumnFormattedStateSet('type', 'Pelunasan', $settlement)
        ->assertTableActionVisible('verify', $settlement)
        ->callTableAction('verify', $settlement)
        ->assertHasNoTableActionErrors();

    expect($settlement->fresh()->status)->toBe(PaymentStatus::Verified)
        ->and($order->fresh()->status)->toBe(OrderStatus::PaidInFull);
});

it('saves the down payment percentage for new reservations', function () {
    Livewire::test(PaymentSettingsPage::class)
        ->assertFormSet(['dp_percentage' => 50])
        ->fillForm(['dp_percentage' => 101])
        ->call('save')
        ->assertHasFormErrors(['dp_percentage'])
        ->fillForm(['dp_percentage' => 40])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(PaymentSettings::class)->downPaymentPercentage())->toBe(40);
});

it('keeps the settings page for admins only', function () {
    $this->actingAs(User::factory()->create());

    $this->get(PaymentSettingsPage::getUrl())->assertForbidden();
});
