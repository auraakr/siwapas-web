<?php

namespace App\Domain\Event\Actions;

use App\Domain\Event\DTOs\EventRegistrationData;
use App\Domain\Event\Enums\EventStatus;
use App\Domain\Event\Exceptions\EventRegistrationClosed;
use App\Domain\Event\Models\Event;
use App\Domain\Event\Models\EventRegistration;
use App\Domain\Payment\Actions\CreateOrder;
use App\Domain\Payment\Enums\OrderKind;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterForEvent
{
    public function __construct(private CreateOrder $createOrder) {}

    /**
     * Free events register immediately without an order; paid events create an
     * order that holds the quota until it is paid or expires.
     */
    public function handle(EventRegistrationData $data): EventRegistration
    {
        return DB::transaction(function () use ($data): EventRegistration {
            $event = Event::query()->whereKey($data->eventId)->lockForUpdate()->firstOrFail();
            $count = count($data->participants);

            if ($count < 1 || ! $this->isOpen($event)) {
                throw EventRegistrationClosed::notOpen();
            }

            if ($event->quota !== null) {
                $taken = (int) $event->registrations()->where('is_active', true)->sum('participant_count');

                if ($taken + $count > $event->quota) {
                    throw EventRegistrationClosed::full();
                }
            }

            $order = $event->fee > 0
                ? $this->createOrder->handle(OrderKind::EventRegistration, $data->customer, $event->fee * $count)
                : null;

            $registration = $event->registrations()->create([
                'order_id' => $order?->id,
                'participant_count' => $count,
                'is_active' => true,
                'registration_code' => 'EVT-'.strtoupper(Str::random(8)),
            ]);

            foreach ($data->participants as $participant) {
                $registration->participants()->create([
                    'name' => $participant['name'],
                    'phone' => $participant['phone'] ?? null,
                ]);
            }

            return $registration->setRelation('order', $order);
        });
    }

    private function isOpen(Event $event): bool
    {
        if ($event->status !== EventStatus::Published) {
            return false;
        }

        $now = now();

        return ($event->registration_opens_at === null || $event->registration_opens_at->lte($now))
            && ($event->registration_closes_at === null || $event->registration_closes_at->gte($now))
            && ($event->ends_at ?? $event->starts_at)->gte($now);
    }
}
