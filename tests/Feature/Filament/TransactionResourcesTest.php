<?php

use App\Domain\Booking\Models\Reservation;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentMethodType;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\BankAccount;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Models\PaymentProof;
use App\Filament\Resources\BankAccountResource\Pages\ManageBankAccounts;
use App\Filament\Resources\PaymentResource\Pages\ManagePayments;
use App\Filament\Resources\ReservationResource\Pages\EditReservation;
use App\Filament\Resources\ReservationResource\Pages\ListReservations;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

function reservationFor(OrderStatus $status): Reservation
{
    return Reservation::factory()->create(['order_id' => Order::factory()->create(['status' => $status])->id]);
}

describe('booking / reservasi', function () {
    it('lists reservations and filters them by order status', function () {
        $pending = reservationFor(OrderStatus::PendingPayment);
        $confirmed = reservationFor(OrderStatus::Confirmed);

        Livewire::test(ListReservations::class)
            ->assertCanSeeTableRecords([$pending, $confirmed])
            ->filterTable('status', 'confirmed')
            ->assertCanSeeTableRecords([$confirmed])
            ->assertCanNotSeeTableRecords([$pending]);
    });

    it('edits only the customer details of the order', function () {
        $reservation = reservationFor(OrderStatus::PendingPayment);
        $total = $reservation->order->total;

        Livewire::test(EditReservation::class, ['record' => $reservation->getRouteKey()])
            ->fillForm(['order.customer_name' => 'Nama Baru', 'order.customer_phone' => '081299998888', 'order.notes' => 'Datang malam'])
            ->call('save')
            ->assertHasNoFormErrors();

        $order = $reservation->order->fresh();
        expect($order->customer_name)->toBe('Nama Baru')
            ->and($order->notes)->toBe('Datang malam')
            ->and($order->total)->toBe($total)
            ->and($order->status)->toBe(OrderStatus::PendingPayment);
    });

    it('validates the customer phone number when editing', function () {
        $reservation = reservationFor(OrderStatus::PendingPayment);

        Livewire::test(EditReservation::class, ['record' => $reservation->getRouteKey()])
            ->fillForm(['order.customer_phone' => '12345'])
            ->call('save')
            ->assertHasFormErrors(['order.customer_phone']);
    });

    it('cancels a reservation, releases the slot and records the admin', function () {
        $reservation = reservationFor(OrderStatus::Confirmed);

        Livewire::test(ListReservations::class)
            ->callTableAction('cancel', $reservation, ['reason' => 'Tamu membatalkan'])
            ->assertHasNoTableActionErrors();

        expect($reservation->order->fresh()->status)->toBe(OrderStatus::Cancelled)
            ->and($reservation->fresh()->is_active)->toBeFalse()
            ->and($reservation->order->statusHistories()->sole()->note)->toBe('Tamu membatalkan');
    });

    it('does not offer cancelling a closed reservation', function () {
        $expired = reservationFor(OrderStatus::Expired);

        Livewire::test(ListReservations::class)->assertTableActionHidden('cancel', $expired);
    });

    it('deletes only closed reservations without any payment proof', function () {
        $active = reservationFor(OrderStatus::Confirmed);
        $cancelled = reservationFor(OrderStatus::Cancelled);
        $cancelledWithProof = reservationFor(OrderStatus::Cancelled);
        PaymentProof::factory()->for(Payment::factory()->for($cancelledWithProof->order)->create())->create();

        Livewire::test(ListReservations::class)
            ->assertTableActionHidden('delete', $active)
            ->assertTableActionHidden('delete', $cancelledWithProof)
            ->assertTableActionVisible('delete', $cancelled)
            ->callTableAction('delete', $cancelled);

        expect(Reservation::count())->toBe(2)
            ->and(Order::whereKey($cancelled->order_id)->exists())->toBeFalse();
    });
});

describe('pembayaran', function () {
    function paymentAwaitingDecision(): Payment
    {
        return orderAwaitingVerification()[1];
    }

    it('verifies a payment and confirms the order', function () {
        $payment = paymentAwaitingDecision();
        $admin = auth()->user();

        Livewire::test(ManagePayments::class)
            ->callTableAction('verify', $payment)
            ->assertHasNoTableActionErrors();

        expect($payment->fresh()->status)->toBe(PaymentStatus::Verified)
            ->and($payment->fresh()->verified_by)->toBe($admin->id)
            ->and($payment->order->fresh()->status)->toBe(OrderStatus::Confirmed);
    });

    it('rejects a payment with a reason and releases the slot', function () {
        $payment = paymentAwaitingDecision();
        $reservation = Reservation::factory()->create(['order_id' => $payment->order_id]);

        Livewire::test(ManagePayments::class)
            ->callTableAction('reject', $payment, ['reason' => 'Nominal tidak sesuai'])
            ->assertHasNoTableActionErrors();

        expect($payment->fresh()->status)->toBe(PaymentStatus::Rejected)
            ->and($payment->fresh()->rejection_reason)->toBe('Nominal tidak sesuai')
            ->and($reservation->fresh()->is_active)->toBeFalse();
    });

    it('requires a reason to reject', function () {
        $payment = paymentAwaitingDecision();

        Livewire::test(ManagePayments::class)
            ->callTableAction('reject', $payment, ['reason' => ''])
            ->assertHasTableActionErrors(['reason']);

        expect($payment->fresh()->status)->toBe(PaymentStatus::Pending);
    });

    it('offers verify and reject only for payments that wait for a decision', function () {
        $waiting = paymentAwaitingDecision();
        $unpaid = Payment::factory()->for(Order::factory()->create())->create();
        $done = Payment::factory()->verified()->for(Order::factory()->confirmed()->create())->create();

        Livewire::test(ManagePayments::class)
            ->assertTableActionVisible('verify', $waiting)
            ->assertTableActionVisible('reject', $waiting)
            ->assertTableActionHidden('verify', $unpaid)
            ->assertTableActionHidden('reject', $done)
            ->assertTableActionDoesNotExist('delete');
    });

    it('shows the proofs of a payment', function () {
        [, $payment, $proof] = orderAwaitingVerification();

        Livewire::test(ManagePayments::class)
            ->mountTableAction('proofs', $payment)
            ->assertSee($proof->original_name);
    });
});

describe('metode pembayaran', function () {
    it('adds a bank account and requires its number and holder', function () {
        Livewire::test(ManageBankAccounts::class)
            ->callAction('create', ['type' => 'bank', 'bank_name' => 'BCA', 'sort_order' => 0])
            ->assertHasActionErrors(['account_number', 'account_holder']);

        Livewire::test(ManageBankAccounts::class)
            ->callAction('create', ['type' => 'bank', 'bank_name' => 'BCA', 'account_number' => '1234567890', 'account_holder' => 'Desa Wisata', 'sort_order' => 0, 'is_active' => true])
            ->assertHasNoActionErrors();

        expect(BankAccount::sole()->type)->toBe(PaymentMethodType::Bank);
    });

    it('requires the QRIS image for a QRIS method', function () {
        Livewire::test(ManageBankAccounts::class)
            ->callAction('create', ['type' => 'qris', 'bank_name' => 'QRIS Desa', 'sort_order' => 0])
            ->assertHasActionErrors(['qris']);

        expect(BankAccount::count())->toBe(0);
    });

    it('toggles, edits and deletes a method', function () {
        $method = BankAccount::factory()->create(['is_active' => true]);

        Livewire::test(ManageBankAccounts::class)
            ->callTableAction('edit', $method, ['type' => 'bank', 'bank_name' => 'BNI', 'account_number' => '555', 'account_holder' => 'Desa', 'sort_order' => 0])
            ->assertHasNoTableActionErrors();
        expect($method->fresh()->bank_name)->toBe('BNI');

        Livewire::test(ManageBankAccounts::class)->callTableAction('delete', $method);
        expect(BankAccount::count())->toBe(0);
    });
});
