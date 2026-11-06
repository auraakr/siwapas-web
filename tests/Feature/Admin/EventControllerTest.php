<?php

use App\Domain\Event\Enums\EventStatus;
use App\Domain\Event\Models\Event;
use App\Domain\Event\Models\EventRegistration;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Models\Order;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array<string, mixed>
 */
function eventForm(array $overrides = []): array
{
    return [
        'title_id' => 'Festival Budaya',
        'title_en' => 'Culture Festival',
        'description_id' => 'Perayaan budaya desa.',
        'starts_at' => '2030-03-10T09:00',
        'ends_at' => '2030-03-10T15:00',
        'fee' => 50000,
        'quota' => 100,
        'status' => 'published',
        ...$overrides,
    ];
}

it('redirects guests to the login page', function () {
    $event = Event::factory()->create();

    $this->get(route('admin.events.index'))->assertRedirect(route('login'));
    $this->get(route('admin.events.create'))->assertRedirect(route('login'));
    $this->post(route('admin.events.store'), eventForm())->assertRedirect(route('login'));
    $this->put(route('admin.events.update', $event), eventForm())->assertRedirect(route('login'));
});

it('forbids registered visitors who are not admins', function () {
    $event = Event::factory()->create();
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.events.index'))->assertForbidden();
    $this->post(route('admin.events.store'), eventForm())->assertForbidden();
    $this->put(route('admin.events.update', $event), eventForm())->assertForbidden();

    expect(Event::count())->toBe(1);
});

it('lists events with the number of active participants', function () {
    $event = Event::factory()->create(['quota' => 50]);
    EventRegistration::factory()->create(['event_id' => $event->id, 'participant_count' => 3]);
    EventRegistration::factory()->create(['event_id' => $event->id, 'participant_count' => 4, 'is_active' => false]);
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.events.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/events/index')
            ->where('events.data.0.taken', 3)
            ->where('events.data.0.quota', 50));
});

describe('store', function () {
    it('creates a bilingual event and stores the Jakarta time as UTC', function () {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->post(route('admin.events.store'), eventForm());

        $event = Event::sole();
        $response->assertRedirect(route('admin.events.edit', $event));
        expect($event->slug)->toBe('festival-budaya')
            ->and($event->getTranslation('title', 'id'))->toBe('Festival Budaya')
            ->and($event->getTranslation('title', 'en'))->toBe('Culture Festival')
            ->and($event->starts_at->toDateTimeString())->toBe('2030-03-10 02:00:00')
            ->and($event->ends_at->toDateTimeString())->toBe('2030-03-10 08:00:00')
            ->and($event->fee)->toBe(50000)
            ->and($event->quota)->toBe(100)
            ->and($event->status)->toBe(EventStatus::Published);
    });

    it('keeps slugs unique for events with the same title', function () {
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('admin.events.store'), eventForm());
        $this->post(route('admin.events.store'), eventForm());

        expect(Event::orderBy('id')->pluck('slug')->all())->toBe(['festival-budaya', 'festival-budaya-2']);
    });

    it('allows an event without a quota and an English title', function () {
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('admin.events.store'), eventForm(['quota' => null, 'title_en' => '']))->assertSessionHasNoErrors();

        $event = Event::sole();
        expect($event->quota)->toBeNull()
            ->and($event->getTranslation('title', 'en', false))->toBe('');
    });

    it('validates the form', function (array $overrides, string $field, string $message) {
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('admin.events.store'), eventForm($overrides))->assertSessionHasErrors([$field => $message]);

        expect(Event::count())->toBe(0);
    })->with([
        'missing title' => [['title_id' => ''], 'title_id', 'Judul (Indonesia) wajib diisi.'],
        'missing start' => [['starts_at' => ''], 'starts_at', 'Waktu mulai wajib diisi.'],
        'ends before it starts' => [['ends_at' => '2030-03-10T08:00'], 'ends_at', 'Waktu selesai harus setelah waktu mulai.'],
        'closes before it opens' => [['registration_opens_at' => '2030-03-01T10:00', 'registration_closes_at' => '2030-03-01T09:00'], 'registration_closes_at', 'Penutupan pendaftaran harus setelah pembukaan.'],
        'missing fee' => [['fee' => ''], 'fee', 'Biaya wajib diisi (isi 0 untuk acara gratis).'],
        'zero quota' => [['quota' => 0], 'quota', 'Kuota minimal 1.'],
    ]);

    it('rejects an unknown status', function () {
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('admin.events.store'), eventForm(['status' => 'bogus']))->assertSessionHasErrors('status');
    });
});

describe('edit and update', function () {
    it('shows the stored time in Jakarta time together with the registrations', function () {
        $event = Event::factory()->create(['starts_at' => '2030-03-10 02:00:00', 'ends_at' => null]);
        EventRegistration::factory()->create(['event_id' => $event->id, 'registration_code' => 'EVT-ABCD1234']);
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.events.edit', $event))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/events/form')
                ->where('event.starts_at', '2030-03-10T09:00')
                ->where('event.ends_at', null)
                ->has('registrations', 1)
                ->where('registrations.0.code', 'EVT-ABCD1234'));
    });

    it('updates the event without changing its slug', function () {
        $event = Event::factory()->create(['slug' => 'lama']);
        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('admin.events.update', $event), eventForm(['title_id' => 'Judul Baru', 'status' => 'cancelled']))
            ->assertRedirect();

        $event->refresh();
        expect($event->slug)->toBe('lama')
            ->and($event->getTranslation('title', 'id'))->toBe('Judul Baru')
            ->and($event->status)->toBe(EventStatus::Cancelled);
    });
});

describe('cancel registration', function () {
    it('cancels a free registration and frees its quota', function () {
        $registration = EventRegistration::factory()->create(['participant_count' => 2]);
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('admin.events.registrations.cancel', [$registration->event, $registration]), ['reason' => 'Berhalangan'])
            ->assertRedirect();

        expect($registration->fresh()->is_active)->toBeFalse();
    });

    it('cancels the order of a paid registration', function () {
        $order = Order::factory()->create(['status' => OrderStatus::Confirmed]);
        $registration = EventRegistration::factory()->create(['order_id' => $order->id]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.events.registrations.cancel', [$registration->event, $registration]), ['reason' => 'Acara diundur'])
            ->assertRedirect();

        expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
            ->and($registration->fresh()->is_active)->toBeFalse()
            ->and($order->statusHistories()->sole()->actor_id)->toBe($admin->id);
    });

    it('requires a reason', function () {
        $registration = EventRegistration::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('admin.events.registrations.cancel', [$registration->event, $registration]), [])
            ->assertSessionHasErrors('reason');

        expect($registration->fresh()->is_active)->toBeTrue();
    });

    it('returns 404 for a registration of another event', function () {
        $registration = EventRegistration::factory()->create();
        $other = Event::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('admin.events.registrations.cancel', [$other, $registration]), ['reason' => 'x'])->assertNotFound();

        expect($registration->fresh()->is_active)->toBeTrue();
    });

    it('reports an order that can no longer be cancelled', function () {
        $order = Order::factory()->create(['status' => OrderStatus::Expired]);
        $registration = EventRegistration::factory()->create(['order_id' => $order->id]);
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('admin.events.registrations.cancel', [$registration->event, $registration]), ['reason' => 'x'])
            ->assertSessionHasErrors(['reason' => 'Pendaftaran ini tidak dapat dibatalkan lagi.']);
    });
});
