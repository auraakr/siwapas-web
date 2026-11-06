<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Event\Actions\CancelEventRegistration;
use App\Domain\Event\Actions\SaveEvent;
use App\Domain\Event\Enums\EventStatus;
use App\Domain\Event\Models\Event;
use App\Domain\Event\Models\EventRegistration;
use App\Domain\Payment\Exceptions\InvalidOrderTransition;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CancelOrderRequest;
use App\Http\Requests\Admin\SaveEventRequest;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/events/index', [
            'events' => Event::query()
                ->withSum(['registrations as taken' => fn ($query) => $query->where('is_active', true)], 'participant_count')
                ->latest('starts_at')
                ->paginate(15)
                ->through(fn (Event $event): array => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'status' => $event->status->value,
                    'starts_at' => $event->starts_at->toIso8601String(),
                    'fee' => $event->fee,
                    'quota' => $event->quota,
                    'taken' => (int) $event->getAttribute('taken'),
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/events/form', [
            'event' => null,
            'registrations' => [],
            'statuses' => array_map(fn (EventStatus $status): string => $status->value, EventStatus::cases()),
        ]);
    }

    public function store(SaveEventRequest $request, SaveEvent $saveEvent): RedirectResponse
    {
        $event = $saveEvent->handle(null, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Acara ditambahkan.']);

        return to_route('admin.events.edit', $event);
    }

    public function edit(Event $event): Response
    {
        $event->load('registrations.participants', 'registrations.order');

        return Inertia::render('admin/events/form', [
            'event' => [
                'id' => $event->id,
                'slug' => $event->slug,
                'title_id' => $event->getTranslation('title', 'id', false),
                'title_en' => $event->getTranslation('title', 'en', false),
                'description_id' => $event->getTranslation('description', 'id', false),
                'description_en' => $event->getTranslation('description', 'en', false),
                'requirements_id' => $event->getTranslation('requirements', 'id', false),
                'requirements_en' => $event->getTranslation('requirements', 'en', false),
                'organizer' => $event->organizer,
                'location_name' => $event->location_name,
                'address' => $event->address,
                'latitude' => $event->latitude,
                'longitude' => $event->longitude,
                'starts_at' => $this->forInput($event->starts_at),
                'ends_at' => $this->forInput($event->ends_at),
                'fee' => $event->fee,
                'quota' => $event->quota,
                'registration_opens_at' => $this->forInput($event->registration_opens_at),
                'registration_closes_at' => $this->forInput($event->registration_closes_at),
                'status' => $event->status->value,
            ],
            'registrations' => $event->registrations->map(fn (EventRegistration $registration): array => [
                'id' => $registration->id,
                'code' => $registration->registration_code,
                'is_active' => $registration->is_active,
                'participant_count' => $registration->participant_count,
                'participants' => $registration->participants->pluck('name')->all(),
                'order_code' => $registration->order?->code,
                'order_status' => $registration->order?->status->value,
                'contact' => $registration->order ? "{$registration->order->customer_name} ({$registration->order->customer_phone})" : null,
            ])->values(),
            'statuses' => array_map(fn (EventStatus $status): string => $status->value, EventStatus::cases()),
        ]);
    }

    public function update(SaveEventRequest $request, Event $event, SaveEvent $saveEvent): RedirectResponse
    {
        $saveEvent->handle($event, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Acara diperbarui.']);

        return back();
    }

    public function cancelRegistration(CancelOrderRequest $request, Event $event, EventRegistration $registration, CancelEventRegistration $cancelEventRegistration): RedirectResponse
    {
        abort_unless($registration->event_id === $event->id, 404);

        try {
            $cancelEventRegistration->handle($registration, $request->user(), $request->string('reason')->toString());
        } catch (InvalidOrderTransition) {
            return back()->withErrors(['reason' => 'Pendaftaran ini tidak dapat dibatalkan lagi.']);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pendaftaran dibatalkan.']);

        return back();
    }

    private function forInput(?CarbonInterface $value): ?string
    {
        return $value?->copy()->setTimezone(config('siwapas.display_timezone'))->format('Y-m-d\TH:i');
    }
}
