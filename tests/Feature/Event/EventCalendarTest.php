<?php

use App\Domain\Event\Models\Event;
use Carbon\CarbonImmutable;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * A Jakarta wall-clock time as the UTC instant the database stores.
 */
function wib(string $time): CarbonImmutable
{
    return CarbonImmutable::parse($time, 'Asia/Jakarta')->utc();
}

function calendarEvent(string $slug, string $startsAt, ?string $endsAt = null, string $status = 'published'): Event
{
    return Event::factory()->create([
        'slug' => $slug,
        'starts_at' => wib($startsAt),
        'ends_at' => $endsAt ? wib($endsAt) : null,
        'status' => $status,
    ]);
}

beforeEach(function () {
    // 01:00 on 1 December in Jakarta, still 30 November in UTC.
    $this->travelTo('2026-11-30 18:00:00');
});

it('opens on the current month in Jakarta', function () {
    $this->get(route('events.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('calendar.month', '2026-12')
            ->where('calendar.previous', '2026-11')
            ->where('calendar.next', '2027-01')
            ->where('calendar.today', '2026-12-01'));
});

it('marks every Jakarta day an event covers, also across months', function () {
    calendarEvent('awal-pagi', '2026-12-01 06:00');
    calendarEvent('lintas-bulan', '2026-11-29 09:00', '2026-12-02 13:00');
    calendarEvent('tahun-baru', '2026-12-30 19:00', '2027-01-02 13:00');
    calendarEvent('sampai-tengah-malam', '2026-12-05 20:00', '2026-12-06 00:00');

    $this->get(route('events.index', ['bulan' => '2026-12']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('calendar.events', 4)
            ->where('calendar.events.0.slug', 'lintas-bulan')
            ->where('calendar.events.0.dates', ['2026-12-01', '2026-12-02'])
            ->where('calendar.events.1.slug', 'awal-pagi')
            ->where('calendar.events.1.dates', ['2026-12-01'])
            ->where('calendar.events.2.dates', ['2026-12-05'])
            ->where('calendar.events.3.slug', 'tahun-baru')
            ->where('calendar.events.3.dates', ['2026-12-30', '2026-12-31']));

    $this->get(route('events.index', ['bulan' => '2027-01']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('calendar.events', 1)
            ->where('calendar.events.0.dates', ['2027-01-01', '2027-01-02']));
});

it('leaves out draft and cancelled events and events of other months', function () {
    calendarEvent('terbit', '2026-12-10 09:00');
    calendarEvent('draf', '2026-12-11 09:00', status: 'draft');
    calendarEvent('batal', '2026-12-12 09:00', status: 'cancelled');
    calendarEvent('bulan-lalu', '2026-11-10 09:00', '2026-11-10 12:00');
    calendarEvent('bulan-depan', '2027-01-10 09:00');

    $this->get(route('events.index', ['bulan' => '2026-12']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('calendar.events', 1)
            ->where('calendar.events.0.slug', 'terbit'));
});

it('falls back to the current month for a month it cannot read', function (string $month) {
    $this->get(route('events.index', ['bulan' => $month]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('calendar.month', '2026-12'));
})->with(['2026-13', 'desember', '1999-01', '2026-1']);

it('reloads only the calendar when the visitor changes month', function () {
    calendarEvent('januari', '2027-01-15 09:00');

    $this->get(route('events.index', ['bulan' => '2027-01']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events')
            ->reloadOnly('calendar', fn (Assert $reload) => $reload
                ->missing('events')
                ->missing('event_venues')
                ->where('calendar.month', '2027-01')
                ->where('calendar.events.0.slug', 'januari')));
});
