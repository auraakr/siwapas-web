<?php

namespace App\Http\Controllers\Public;

use App\Domain\Event\Enums\EventStatus;
use App\Domain\Event\Models\Event;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('public/events/index', [
            'events' => Event::query()
                ->where('status', EventStatus::Published)
                ->where('starts_at', '>=', now()->startOfDay())
                ->orderBy('starts_at')
                ->get()
                ->map(fn (Event $event): array => [
                    'slug' => $event->slug,
                    'title' => $event->title,
                    'starts_at' => $event->starts_at->toIso8601String(),
                    'location_name' => $event->location_name,
                    'fee' => $event->fee,
                ]),
        ]);
    }

    public function show(Event $event): Response
    {
        abort_unless($event->status === EventStatus::Published, 404);

        $taken = (int) $event->registrations()->where('is_active', true)->sum('participant_count');

        return Inertia::render('public/events/show', [
            'event' => [
                'slug' => $event->slug,
                'title' => $event->title,
                'description' => $event->description,
                'organizer' => $event->organizer,
                'location_name' => $event->location_name,
                'address' => $event->address,
                'starts_at' => $event->starts_at->toIso8601String(),
                'ends_at' => $event->ends_at?->toIso8601String(),
                'fee' => $event->fee,
                'requirements' => $event->requirements,
                'remaining_quota' => $event->quota === null ? null : max(0, $event->quota - $taken),
            ],
            'registration_code' => session('registration_code'),
        ]);
    }
}
