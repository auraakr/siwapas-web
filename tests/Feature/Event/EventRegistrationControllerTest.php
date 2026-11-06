<?php

use App\Domain\Event\Enums\EventStatus;
use App\Domain\Event\Models\Event;
use App\Domain\Event\Models\EventRegistration;
use App\Domain\Payment\Enums\OrderKind;
use App\Domain\Payment\Models\BankAccount;
use App\Domain\Payment\Models\Order;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array<string, mixed>
 */
function eventPayload(int $participants = 2): array
{
    return [
        'customer_name' => 'Siti Aminah',
        'customer_phone' => '081234567890',
        'participants' => array_map(fn (int $number) => ['name' => "Peserta {$number}"], range(1, $participants)),
    ];
}

it('lists only published upcoming events', function () {
    Event::factory()->create(['slug' => 'festival', 'status' => EventStatus::Published]);
    Event::factory()->create(['slug' => 'draft', 'status' => EventStatus::Draft]);
    Event::factory()->create(['slug' => 'lalu', 'status' => EventStatus::Published, 'starts_at' => now()->subWeek()]);

    $this->get(route('events.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/events/index')
            ->has('events', 1)
            ->where('events.0.slug', 'festival'));
});

it('returns 404 for an unpublished event', function () {
    $event = Event::factory()->create(['status' => EventStatus::Draft]);

    $this->get(route('events.show', $event))->assertNotFound();
});

it('registers for a free event without creating an order', function () {
    $event = Event::factory()->create(['fee' => 0, 'quota' => 10]);

    $response = $this->post(route('events.register', $event), eventPayload(2));

    $registration = EventRegistration::sole();
    $response->assertRedirect(route('events.show', $event))->assertSessionHas('registration_code', $registration->registration_code);
    expect($registration->order_id)->toBeNull()
        ->and($registration->participant_count)->toBe(2)
        ->and($registration->participants()->pluck('name')->all())->toBe(['Peserta 1', 'Peserta 2'])
        ->and(Order::count())->toBe(0);
});

it('creates a payable order for a paid event', function () {
    BankAccount::factory()->create();
    $event = Event::factory()->create(['fee' => 100000, 'quota' => 10]);

    $response = $this->post(route('events.register', $event), eventPayload(3));

    $order = Order::sole();
    $response->assertRedirect(route('orders.show', $order));
    expect($order->kind)->toBe(OrderKind::EventRegistration)
        ->and($order->subtotal)->toBe(300000)
        ->and($order->eventRegistration->event_id)->toBe($event->id)
        ->and($order->eventRegistration->is_active)->toBeTrue();
});

it('rejects registrations that exceed the remaining quota', function () {
    $event = Event::factory()->create(['fee' => 0, 'quota' => 3]);
    EventRegistration::factory()->create(['event_id' => $event->id, 'participant_count' => 2]);
    EventRegistration::factory()->create(['event_id' => $event->id, 'participant_count' => 5, 'is_active' => false]);

    $this->post(route('events.register', $event), eventPayload(2))
        ->assertSessionHasErrors('participants');

    expect(EventRegistration::count())->toBe(2);
});

it('counts inactive registrations as free quota', function () {
    $event = Event::factory()->create(['fee' => 0, 'quota' => 3]);
    EventRegistration::factory()->create(['event_id' => $event->id, 'participant_count' => 3, 'is_active' => false]);

    $this->post(route('events.register', $event), eventPayload(3))->assertSessionHasNoErrors();

    expect(EventRegistration::where('is_active', true)->sum('participant_count'))->toEqual(3);
});

it('rejects registration outside the registration window', function (string $column, string $offset) {
    $event = Event::factory()->create(['fee' => 0, $column => now()->modify($offset)]);

    $this->post(route('events.register', $event), eventPayload(1))->assertSessionHasErrors('participants');

    expect(EventRegistration::count())->toBe(0);
})->with([
    'not yet open' => ['registration_opens_at', '+1 day'],
    'already closed' => ['registration_closes_at', '-1 day'],
]);

it('validates the participants', function () {
    $event = Event::factory()->create();

    $this->post(route('events.register', $event), [])->assertSessionHasErrors(['customer_name', 'customer_phone', 'participants']);
    $this->post(route('events.register', $event), [...eventPayload(1), 'participants' => [['name' => '']]])
        ->assertSessionHasErrors(['participants.0.name' => 'Nama peserta wajib diisi.']);
    $this->post(route('events.register', $event), eventPayload(11))->assertSessionHasErrors('participants');
});
