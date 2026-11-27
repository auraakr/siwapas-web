<?php

namespace App\Domain\Event\Actions;

use App\Domain\Event\Enums\EventStatus;
use App\Domain\Event\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class BuildEventCalendar
{
    /**
     * Published events of one month (Asia/Jakarta) with every day each event covers, for the
     * calendar on /acara. Day boundaries are worked out here in PHP and the query only compares
     * plain UTC timestamps, so it behaves the same on PostgreSQL, MySQL and SQLite.
     *
     * @return array{
     *     month: string,
     *     previous: string,
     *     next: string,
     *     today: string,
     *     events: list<array{slug: string, title: string, starts_at: string, ends_at: string|null, location_name: string|null, fee: int, dates: list<string>}>
     * }
     */
    public function handle(?string $month): array
    {
        $zone = (string) config('siwapas.display_timezone');
        $today = CarbonImmutable::now($zone);
        $first = $this->firstDayOf($month, $zone) ?? $today->startOfMonth();
        $last = $first->endOfMonth();

        $from = $first->utc();
        $until = $last->utc();

        $events = array_values(Event::query()
            ->where('status', EventStatus::Published)
            ->where('starts_at', '<=', $until)
            ->where(fn (Builder $query) => $query
                ->where('ends_at', '>=', $from)
                ->orWhere(fn (Builder $query) => $query->whereNull('ends_at')->where('starts_at', '>=', $from)))
            ->orderBy('starts_at')
            ->orderBy('id')
            ->get()
            ->map(fn (Event $event): array => [
                'slug' => $event->slug,
                'title' => $event->title,
                'starts_at' => $event->starts_at->toIso8601String(),
                'ends_at' => $event->ends_at?->toIso8601String(),
                'location_name' => $event->location_name,
                'fee' => $event->fee,
                'dates' => $this->datesWithin($event, $first, $last, $zone),
            ])
            ->all());

        return [
            'month' => $first->format('Y-m'),
            'previous' => $first->subMonthNoOverflow()->format('Y-m'),
            'next' => $first->addMonthNoOverflow()->format('Y-m'),
            'today' => $today->toDateString(),
            'events' => $events,
        ];
    }

    /**
     * The first day of a "YYYY-MM" month, or null for anything else.
     */
    private function firstDayOf(?string $month, string $zone): ?CarbonImmutable
    {
        if ($month === null || ! preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $month, $match)) {
            return null;
        }

        $year = (int) $match[1];

        if ($year < 2000 || $year > 2100) {
            return null;
        }

        return CarbonImmutable::create($year, (int) $match[2], 1, 0, 0, 0, $zone);
    }

    /**
     * Every Jakarta date the event covers inside the month (an event ending exactly at midnight
     * does not take the next day).
     *
     * @return list<string>
     */
    private function datesWithin(Event $event, CarbonImmutable $first, CarbonImmutable $last, string $zone): array
    {
        $start = $event->starts_at->setTimezone($zone);
        $end = ($event->ends_at ?? $event->starts_at)->setTimezone($zone);

        if ($end->gt($start) && $end->format('H:i:s') === '00:00:00') {
            $end = $end->subSecond();
        }

        $day = $start->startOfDay()->max($first->startOfDay());
        $lastDay = $end->startOfDay()->min($last->startOfDay());
        $dates = [];

        while ($day->lte($lastDay)) {
            $dates[] = $day->toDateString();
            $day = $day->addDay();
        }

        return $dates;
    }
}
