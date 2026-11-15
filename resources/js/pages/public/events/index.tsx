import { Head, Link } from '@inertiajs/react';
import { ArrowRight, MapPin } from 'lucide-react';
import PageTitle from '@/components/site/page-title';
import { formatDateTime, formatRupiah } from '@/lib/format';
import { useTranslation } from '@/lib/i18n';
import { show } from '@/routes/events';

type EventSummary = {
    slug: string;
    title: string;
    starts_at: string;
    location_name: string | null;
    fee: number;
};

export default function EventsIndex({ events }: { events: EventSummary[] }) {
    const { t, locale } = useTranslation();
    const tag = locale === 'en' ? 'en-GB' : 'id-ID';
    // Fixed zone: the server and the browser must print the same day, or hydration complains.
    const dayOf = (iso: string) =>
        new Intl.DateTimeFormat(tag, {
            timeZone: 'Asia/Jakarta',
            day: 'numeric',
        }).format(new Date(iso));
    const monthOf = (iso: string) =>
        new Intl.DateTimeFormat(tag, {
            timeZone: 'Asia/Jakarta',
            month: 'short',
        }).format(new Date(iso));

    return (
        <>
            <Head title={t('nav.events')}>
                <meta
                    name="description"
                    content={t('events.pageDescription')}
                />
            </Head>

            <PageTitle
                eyebrow={t('events.eyebrow')}
                title={t('nav.events')}
                description={t('events.pageDescription')}
            />

            {events.length === 0 ? (
                <p className="rounded-2xl border bg-white p-8 text-center text-muted-foreground">
                    {t('events.none')}
                </p>
            ) : (
                <ul className="grid gap-4 sm:grid-cols-2">
                    {events.map((event) => (
                        <li key={event.slug}>
                            <Link
                                href={show(event.slug)}
                                className="group flex h-full gap-4 rounded-2xl border bg-white p-5 shadow-sm transition hover:border-forest-600 focus-visible:ring-2 focus-visible:ring-forest-600 focus-visible:outline-none"
                            >
                                <div
                                    aria-hidden="true"
                                    className="flex size-16 shrink-0 flex-col items-center justify-center rounded-xl bg-forest-100 text-forest-900"
                                >
                                    <span className="font-serif text-2xl leading-none font-semibold">
                                        {dayOf(event.starts_at)}
                                    </span>
                                    <span className="mt-1 text-[11px] font-semibold tracking-wider uppercase">
                                        {monthOf(event.starts_at)}
                                    </span>
                                </div>
                                <div className="min-w-0 flex-1">
                                    <h2 className="font-serif text-lg leading-snug font-semibold text-forest-900">
                                        {event.title}
                                    </h2>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {formatDateTime(event.starts_at)}
                                    </p>
                                    {event.location_name && (
                                        <p className="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground">
                                            <MapPin
                                                className="size-3.5 shrink-0"
                                                aria-hidden="true"
                                            />
                                            {event.location_name}
                                        </p>
                                    )}
                                    <p className="mt-3 flex items-center justify-between gap-3 text-sm">
                                        <span className="font-semibold text-forest-900">
                                            {event.fee > 0
                                                ? formatRupiah(event.fee)
                                                : t('events.free')}
                                        </span>
                                        <span className="inline-flex items-center gap-1.5 font-semibold text-clay-dark transition group-hover:gap-2.5">
                                            {t('events.register')}
                                            <ArrowRight
                                                className="size-4"
                                                aria-hidden="true"
                                            />
                                        </span>
                                    </p>
                                </div>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </>
    );
}
