import { Link } from '@inertiajs/react';
import { Clock, MapPin } from 'lucide-react';
import SectionHeading from '@/components/landing/section-heading';
import { dateParts, formatRupiah, formatTime } from '@/lib/format';
import { useTranslation } from '@/lib/i18n';
import events from '@/routes/events';
import type { EventSummary } from '@/types/landing';

export default function Events({ items }: { items: EventSummary[] }) {
    const { t, locale } = useTranslation();

    if (items.length === 0) {
        return null;
    }

    return (
        <section id="acara" className="scroll-mt-24 bg-cream-dark py-20">
            <div className="mx-auto max-w-5xl px-4">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <SectionHeading
                        eyebrow={t('events.eyebrow')}
                        title={t('events.title')}
                    />
                    <Link
                        href={events.index()}
                        className="text-sm font-semibold text-forest-800 underline-offset-4 hover:text-clay hover:underline"
                    >
                        {t('events.all')}
                    </Link>
                </div>

                <ul className="mt-10 space-y-4">
                    {items.map((event) => {
                        const { day, month } = dateParts(
                            event.starts_at,
                            locale,
                        );

                        return (
                            <li
                                key={event.slug}
                                className="flex flex-wrap items-center gap-5 rounded-2xl border border-forest-100 bg-white p-4 shadow-sm sm:flex-nowrap"
                            >
                                <div className="flex size-16 shrink-0 flex-col items-center justify-center rounded-xl bg-forest-800 text-cream">
                                    <span className="font-serif text-2xl leading-none font-semibold">
                                        {day}
                                    </span>
                                    <span className="mt-1 text-[10px] tracking-widest">
                                        {month}
                                    </span>
                                </div>

                                <div className="min-w-0 flex-1">
                                    <h3 className="font-serif text-lg font-semibold text-forest-900">
                                        {event.title}
                                    </h3>
                                    <p className="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-forest-800/80">
                                        <span className="flex items-center gap-1.5">
                                            <Clock className="size-3.5" />
                                            {formatTime(
                                                event.starts_at,
                                                locale,
                                            )}{' '}
                                            WIB
                                        </span>
                                        {event.location_name && (
                                            <span className="flex items-center gap-1.5">
                                                <MapPin className="size-3.5" />
                                                {event.location_name}
                                            </span>
                                        )}
                                    </p>
                                </div>

                                <span className="rounded-full bg-forest-50 px-3 py-1 text-xs font-semibold text-forest-800">
                                    {event.fee > 0
                                        ? formatRupiah(event.fee)
                                        : t('events.free')}
                                </span>

                                <Link
                                    href={events.show(event.slug)}
                                    className="rounded-md bg-clay px-5 py-2.5 text-xs font-semibold tracking-wide text-white uppercase transition hover:bg-clay-dark"
                                >
                                    {t('events.register')}
                                </Link>
                            </li>
                        );
                    })}
                </ul>
            </div>
        </section>
    );
}
