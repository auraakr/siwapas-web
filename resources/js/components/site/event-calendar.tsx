import { Link, router } from '@inertiajs/react';
import {
    ArrowRight,
    CalendarDays,
    ChevronLeft,
    ChevronRight,
    Clock,
    MapPin,
} from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { show } from '@/routes/events';

export type CalendarEvent = {
    slug: string;
    title: string;
    starts_at: string;
    ends_at: string | null;
    location_name: string | null;
    fee: number;
    dates: string[];
};

export type EventCalendarData = {
    month: string;
    previous: string;
    next: string;
    today: string;
    events: CalendarEvent[];
};

const ZONE = 'Asia/Jakarta';

/** Days of a "YYYY-MM" month as "YYYY-MM-DD", plus how many blank cells precede day 1 (Monday first). */
function monthDays(month: string): { days: string[]; offset: number } {
    const [year, monthNumber] = month.split('-').map(Number);
    const count = new Date(Date.UTC(year, monthNumber, 0)).getUTCDate();
    const firstWeekday = new Date(
        Date.UTC(year, monthNumber - 1, 1),
    ).getUTCDay();

    return {
        days: Array.from(
            { length: count },
            (_, index) => `${month}-${String(index + 1).padStart(2, '0')}`,
        ),
        offset: (firstWeekday + 6) % 7,
    };
}

function useCalendarFormat() {
    const { locale } = useTranslation();
    const tag = locale === 'en' ? 'en-GB' : 'id-ID';

    return {
        monthTitle: (month: string) =>
            new Intl.DateTimeFormat(tag, {
                month: 'long',
                year: 'numeric',
                timeZone: 'UTC',
            }).format(new Date(`${month}-01T00:00:00Z`)),
        weekdays: Array.from({ length: 7 }, (_, index) =>
            new Intl.DateTimeFormat(tag, {
                weekday: 'short',
                timeZone: 'UTC',
            }).format(new Date(Date.UTC(2024, 0, 1 + index))),
        ),
        longDate: (date: string) =>
            new Intl.DateTimeFormat(tag, {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                timeZone: 'UTC',
            }).format(new Date(`${date}T00:00:00Z`)),
        time: (iso: string) =>
            `${new Intl.DateTimeFormat(tag, {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
                timeZone: ZONE,
            }).format(new Date(iso))} WIB`,
        shortDate: (iso: string) =>
            new Intl.DateTimeFormat(tag, {
                day: 'numeric',
                month: 'short',
                timeZone: ZONE,
            }).format(new Date(iso)),
    };
}

function EventItem({ event }: { event: CalendarEvent }) {
    const { t } = useTranslation();
    const format = useCalendarFormat();
    const multiDay = event.ends_at
        ? format.shortDate(event.starts_at) !== format.shortDate(event.ends_at)
        : false;

    return (
        <li>
            <Link
                href={show(event.slug)}
                className="group block rounded-xl border border-forest-100 bg-white p-4 shadow-sm transition hover:border-forest-600 focus-visible:ring-2 focus-visible:ring-forest-600 focus-visible:outline-none"
            >
                <p className="font-serif text-lg leading-snug font-semibold text-forest-900">
                    {event.title}
                </p>
                <p className="mt-1 flex items-center gap-1.5 text-sm text-forest-800/80">
                    <Clock className="size-3.5 shrink-0" aria-hidden="true" />
                    {multiDay && event.ends_at
                        ? `${format.shortDate(event.starts_at)}, ${format.time(event.starts_at)} – ${format.shortDate(event.ends_at)}, ${format.time(event.ends_at)}`
                        : `${format.time(event.starts_at)}${event.ends_at ? ` – ${format.time(event.ends_at)}` : ''}`}
                </p>
                {event.location_name && (
                    <p className="mt-1 flex items-center gap-1.5 text-sm text-forest-800/80">
                        <MapPin
                            className="size-3.5 shrink-0"
                            aria-hidden="true"
                        />
                        {event.location_name}
                    </p>
                )}
                <span className="mt-2 inline-flex items-center gap-1.5 text-sm font-semibold text-clay-dark transition group-hover:gap-2.5">
                    {t('calendar.details')}
                    <ArrowRight className="size-4" aria-hidden="true" />
                </span>
            </Link>
        </li>
    );
}

/**
 * A month of events. Desktop: a grid where marked days open their events; phones: an agenda of
 * the days that have events. Changing month reloads only this prop from the server.
 */
export default function EventCalendar({
    calendar,
}: {
    calendar: EventCalendarData;
}) {
    const { t } = useTranslation();
    const format = useCalendarFormat();
    const [loading, setLoading] = useState(false);
    const { days, offset } = monthDays(calendar.month);

    const byDate = new Map<string, CalendarEvent[]>();

    for (const event of calendar.events) {
        for (const date of event.dates) {
            byDate.set(date, [...(byDate.get(date) ?? []), event]);
        }
    }

    const defaultDate = days.includes(calendar.today)
        ? calendar.today
        : (days.find((day) => byDate.has(day)) ?? days[0]);
    const [picked, setPicked] = useState<{ month: string; date: string }>();
    const selected =
        picked?.month === calendar.month ? picked.date : defaultDate;
    const selectedEvents = byDate.get(selected) ?? [];
    const agendaDays = days.filter((day) => byDate.has(day));

    const goTo = (month: string, date?: string) => {
        router.reload({
            only: ['calendar'],
            data: { bulan: month },
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
            onSuccess: () => {
                if (date) {
                    setPicked({ month, date });
                }
            },
        });
    };

    const navButton =
        'inline-flex size-10 items-center justify-center rounded-full border border-forest-200 bg-white text-forest-900 transition hover:bg-forest-50 focus-visible:ring-2 focus-visible:ring-forest-600 focus-visible:outline-none';

    return (
        <section
            id="kalender"
            aria-labelledby="kalender-title"
            className={cn('scroll-mt-28', loading && 'opacity-60 transition')}
        >
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h2
                    id="kalender-title"
                    className="flex items-center gap-2 font-serif text-2xl font-semibold text-forest-900 capitalize"
                >
                    <CalendarDays
                        className="size-6 text-clay-dark"
                        aria-hidden="true"
                    />
                    {format.monthTitle(calendar.month)}
                </h2>
                <div className="flex items-center gap-2">
                    <button
                        type="button"
                        onClick={() => goTo(calendar.previous)}
                        aria-label={t('calendar.previous')}
                        className={navButton}
                    >
                        <ChevronLeft className="size-5" aria-hidden="true" />
                    </button>
                    <button
                        type="button"
                        onClick={() =>
                            goTo(calendar.today.slice(0, 7), calendar.today)
                        }
                        className="h-10 rounded-full border border-forest-200 bg-white px-4 text-sm font-semibold text-forest-900 transition hover:bg-forest-50"
                    >
                        {t('calendar.today')}
                    </button>
                    <button
                        type="button"
                        onClick={() => goTo(calendar.next)}
                        aria-label={t('calendar.next')}
                        className={navButton}
                    >
                        <ChevronRight className="size-5" aria-hidden="true" />
                    </button>
                </div>
            </div>

            {/* Desktop: month grid + the picked day. */}
            <div className="mt-6 hidden gap-6 md:grid lg:grid-cols-[1.6fr_1fr]">
                <div className="rounded-2xl border border-forest-100 bg-white p-4 shadow-sm">
                    <div className="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-forest-700 uppercase">
                        {format.weekdays.map((weekday) => (
                            <div key={weekday} className="py-2">
                                {weekday}
                            </div>
                        ))}
                    </div>
                    <div className="grid grid-cols-7 gap-1">
                        {Array.from({ length: offset }, (_, index) => (
                            <div key={`blank-${index}`} aria-hidden="true" />
                        ))}
                        {days.map((day) => {
                            const count = byDate.get(day)?.length ?? 0;
                            const isSelected = day === selected;

                            return (
                                <button
                                    key={day}
                                    type="button"
                                    onClick={() =>
                                        setPicked({
                                            month: calendar.month,
                                            date: day,
                                        })
                                    }
                                    aria-pressed={isSelected}
                                    aria-label={`${format.longDate(day)}${count > 0 ? `, ${t('calendar.eventCount', { count })}` : ''}`}
                                    className={cn(
                                        'flex h-14 flex-col items-center justify-center gap-1 rounded-xl text-sm transition focus-visible:ring-2 focus-visible:ring-forest-600 focus-visible:outline-none',
                                        isSelected
                                            ? 'bg-forest-800 text-cream'
                                            : count > 0
                                              ? 'bg-forest-50 font-semibold text-forest-900 hover:bg-forest-100'
                                              : 'text-forest-800/70 hover:bg-forest-50',
                                        day === calendar.today &&
                                            !isSelected &&
                                            'ring-2 ring-clay',
                                    )}
                                >
                                    {Number(day.slice(8))}
                                    {count > 0 && (
                                        <span
                                            aria-hidden="true"
                                            className={cn(
                                                'flex gap-0.5',
                                                isSelected
                                                    ? 'text-brand-yellow'
                                                    : 'text-clay',
                                            )}
                                        >
                                            {Array.from(
                                                { length: Math.min(count, 3) },
                                                (_, index) => (
                                                    <span
                                                        key={index}
                                                        className="size-1.5 rounded-full bg-current"
                                                    />
                                                ),
                                            )}
                                        </span>
                                    )}
                                </button>
                            );
                        })}
                    </div>
                </div>

                <div aria-live="polite">
                    <h3 className="font-semibold text-forest-900">
                        {format.longDate(selected)}
                    </h3>
                    {selectedEvents.length > 0 ? (
                        <ul className="mt-3 space-y-3">
                            {selectedEvents.map((event) => (
                                <EventItem key={event.slug} event={event} />
                            ))}
                        </ul>
                    ) : (
                        <p className="mt-3 rounded-xl border border-dashed border-forest-200 p-5 text-sm text-forest-800/70">
                            {t('calendar.noEventsDay')}
                        </p>
                    )}
                </div>
            </div>

            {/* Phones: an agenda of the days that have events. */}
            <div className="mt-6 md:hidden">
                {agendaDays.length > 0 ? (
                    <ol className="space-y-6">
                        {agendaDays.map((day) => (
                            <li key={day}>
                                <h3
                                    className={cn(
                                        'text-sm font-semibold text-forest-900',
                                        day === calendar.today &&
                                            'text-clay-dark',
                                    )}
                                >
                                    {format.longDate(day)}
                                    {day === calendar.today &&
                                        ` · ${t('calendar.today')}`}
                                </h3>
                                <ul className="mt-2 space-y-3">
                                    {(byDate.get(day) ?? []).map((event) => (
                                        <EventItem
                                            key={event.slug}
                                            event={event}
                                        />
                                    ))}
                                </ul>
                            </li>
                        ))}
                    </ol>
                ) : (
                    <p className="rounded-xl border border-dashed border-forest-200 p-5 text-sm text-forest-800/70">
                        {t('calendar.noEventsMonth')}
                    </p>
                )}
            </div>
        </section>
    );
}
