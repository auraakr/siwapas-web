<?php

namespace App\Domain\Event\Actions;

use App\Domain\Event\Enums\EventStatus;
use App\Domain\Event\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class SaveEvent
{
    /**
     * Date-time inputs arrive in the display timezone (Asia/Jakarta) and are stored in UTC.
     *
     * @param  array<string, mixed>  $data  Validated admin form input.
     */
    public function handle(?Event $event, array $data): Event
    {
        $event ??= new Event(['slug' => $this->uniqueSlug((string) $data['title_id'])]);

        $event->fill([
            'title' => $this->translations($data, 'title'),
            'description' => $this->translations($data, 'description'),
            'requirements' => $this->translations($data, 'requirements'),
            'organizer' => $data['organizer'] ?? null,
            'location_name' => $data['location_name'] ?? null,
            'address' => $data['address'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'starts_at' => $this->toUtc((string) $data['starts_at']),
            'ends_at' => $this->toUtc($data['ends_at'] ?? null),
            'fee' => (int) $data['fee'],
            'quota' => isset($data['quota']) ? (int) $data['quota'] : null,
            'registration_opens_at' => $this->toUtc($data['registration_opens_at'] ?? null),
            'registration_closes_at' => $this->toUtc($data['registration_closes_at'] ?? null),
            'status' => EventStatus::from((string) $data['status']),
        ])->save();

        return $event;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function translations(array $data, string $field): array
    {
        return array_filter([
            'id' => $data["{$field}_id"] ?? null,
            'en' => $data["{$field}_en"] ?? null,
        ], fn (mixed $value): bool => is_string($value) && $value !== '');
    }

    private function toUtc(?string $value): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Date::createFromFormat('Y-m-d\TH:i', $value, config('siwapas.display_timezone'))->utc();
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'acara';
        $slug = $base;

        for ($suffix = 2; Event::query()->where('slug', $slug)->exists(); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
