import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
    Clock,
    ExternalLink,
    MapPin,
    MessageCircle,
    Play,
} from 'lucide-react';
import { useState } from 'react';
import Media from '@/components/landing/media';
import SectionHeading from '@/components/landing/section-heading';
import DestinationCard from '@/components/site/destination-card';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { withWhatsappText } from '@/lib/whatsapp';
import destinationRoutes from '@/routes/destinations';
import type {
    DestinationCard as DestinationCardData,
    DestinationDetail,
} from '@/types/site';

const actionButton =
    'flex w-full items-center justify-center gap-2 rounded-md px-5 py-3 text-xs font-semibold tracking-wider uppercase transition focus-visible:ring-2 focus-visible:ring-forest-600 focus-visible:ring-offset-2 focus-visible:outline-none';

export default function DestinationShow({
    destination,
    related,
}: {
    destination: DestinationDetail;
    related: DestinationCardData[];
}) {
    const { site } = usePage().props;
    const { t, locale } = useTranslation();
    const [selected, setSelected] = useState(0);
    const hero = destination.images[selected] ?? null;
    const whatsappUrl = site.contact.whatsapp_url;
    const description =
        destination.summary ?? t('destinations.indexDescription');

    const structuredData = {
        '@context': 'https://schema.org',
        '@type': 'TouristAttraction',
        name: destination.name,
        description,
        inLanguage: locale,
        ...(destination.images[0] && { image: destination.images[0] }),
        ...(destination.address && { address: destination.address }),
    };

    return (
        <>
            <Head title={destination.name}>
                <meta name="description" content={description} />
                <meta property="og:title" content={destination.name} />
                <meta property="og:description" content={description} />
                {destination.images[0] && (
                    <meta property="og:image" content={destination.images[0]} />
                )}
                <script type="application/ld+json">
                    {JSON.stringify(structuredData)}
                </script>
            </Head>

            <section className="relative isolate overflow-hidden bg-forest-900 text-cream">
                <div className="absolute inset-0 -z-20">
                    <Media src={hero} alt={destination.name} />
                </div>
                <div className="absolute inset-0 -z-10 bg-linear-to-t from-forest-950/90 via-forest-950/50 to-forest-950/30" />
                <div className="mx-auto flex min-h-[380px] max-w-6xl flex-col justify-end px-4 pt-24 pb-10 sm:px-6 md:min-h-[460px]">
                    <Link
                        href={destinationRoutes.index()}
                        className="mb-6 inline-flex w-fit items-center gap-2 text-sm font-medium text-forest-100 transition hover:text-cream"
                    >
                        <ArrowLeft className="size-4" aria-hidden="true" />
                        {t('destinations.back')}
                    </Link>
                    <p className="mb-3 text-xs font-semibold tracking-[0.25em] text-brand-yellow uppercase">
                        {t(
                            destination.type === 'activity'
                                ? 'destinations.type.activity'
                                : 'destinations.type.destination',
                        )}
                    </p>
                    <h1 className="max-w-3xl font-serif text-4xl leading-tight font-semibold md:text-5xl">
                        {destination.name}
                    </h1>
                    {destination.summary && (
                        <p className="mt-4 max-w-2xl leading-relaxed text-forest-100 md:text-lg">
                            {destination.summary}
                        </p>
                    )}
                </div>
            </section>

            <section className="bg-cream py-12 md:py-16">
                <div className="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-[1.6fr_1fr]">
                    <div>
                        {destination.images.length > 1 && (
                            <ul className="mb-8 flex gap-2 overflow-x-auto pb-2">
                                {destination.images.map((src, index) => (
                                    <li key={src} className="shrink-0">
                                        <button
                                            type="button"
                                            aria-pressed={index === selected}
                                            aria-label={t(
                                                'destinations.photo',
                                                {
                                                    name: `${destination.name} ${index + 1}`,
                                                },
                                            )}
                                            onClick={() => setSelected(index)}
                                            className={cn(
                                                'block size-20 overflow-hidden rounded-lg border-2 transition focus-visible:ring-2 focus-visible:ring-forest-600 focus-visible:outline-none',
                                                index === selected
                                                    ? 'border-clay-dark'
                                                    : 'border-transparent opacity-80 hover:opacity-100',
                                            )}
                                        >
                                            <img
                                                src={src}
                                                alt=""
                                                loading="lazy"
                                                className="size-full object-cover"
                                            />
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        )}

                        <h2 className="mb-4 font-serif text-2xl font-semibold text-forest-900">
                            {t('destinations.about')}
                        </h2>
                        {destination.description ? (
                            <div
                                className="rich-text"
                                dangerouslySetInnerHTML={{
                                    __html: destination.description,
                                }}
                            />
                        ) : (
                            <p className="leading-relaxed text-forest-800/80">
                                {destination.summary}
                            </p>
                        )}
                    </div>

                    <aside className="h-fit space-y-6 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm lg:sticky lg:top-32">
                        {destination.requires_reservation && (
                            <p className="rounded-lg bg-brand-yellow/30 px-3 py-2 text-sm font-medium text-forest-950">
                                {t('activities.needsReservation')}
                            </p>
                        )}

                        {destination.address && (
                            <div>
                                <h3 className="mb-2 flex items-center gap-2 text-sm font-semibold text-forest-900">
                                    <MapPin
                                        className="size-4 text-clay-dark"
                                        aria-hidden="true"
                                    />
                                    {t('destinations.address')}
                                </h3>
                                <p className="text-sm text-forest-800/90">
                                    {destination.address}
                                </p>
                            </div>
                        )}

                        {destination.opening_hours.length > 0 && (
                            <div>
                                <h3 className="mb-2 flex items-center gap-2 text-sm font-semibold text-forest-900">
                                    <Clock
                                        className="size-4 text-clay-dark"
                                        aria-hidden="true"
                                    />
                                    {t('destinations.openingHours')}
                                </h3>
                                <dl className="space-y-1 text-sm">
                                    {destination.opening_hours.map((row) => (
                                        <div
                                            key={row.day}
                                            className="flex justify-between gap-4"
                                        >
                                            <dt className="text-forest-800/80">
                                                {row.day}
                                            </dt>
                                            <dd className="font-medium text-forest-900">
                                                {row.hours}
                                            </dd>
                                        </div>
                                    ))}
                                </dl>
                            </div>
                        )}

                        {destination.facilities.length > 0 && (
                            <div>
                                <h3 className="mb-2 text-sm font-semibold text-forest-900">
                                    {t('destinations.facilities')}
                                </h3>
                                <ul className="flex flex-wrap gap-2">
                                    {destination.facilities.map((item) => (
                                        <li
                                            key={item}
                                            className="rounded-full bg-forest-100 px-3 py-1 text-xs font-medium text-forest-900"
                                        >
                                            {item}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        {destination.rules.length > 0 && (
                            <div>
                                <h3 className="mb-2 text-sm font-semibold text-forest-900">
                                    {t('destinations.rules')}
                                </h3>
                                <ul className="list-disc space-y-1 pl-5 text-sm text-forest-800/90">
                                    {destination.rules.map((rule) => (
                                        <li key={rule}>{rule}</li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        <div className="space-y-3 border-t border-forest-100 pt-6">
                            {whatsappUrl && (
                                <a
                                    href={withWhatsappText(
                                        whatsappUrl,
                                        t('activities.askMessage', {
                                            name: destination.name,
                                        }),
                                    )}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className={cn(
                                        actionButton,
                                        'bg-clay-dark text-white hover:bg-clay',
                                    )}
                                >
                                    <MessageCircle
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                    {t('activities.ask')}
                                </a>
                            )}
                            {destination.maps_url && (
                                <a
                                    href={destination.maps_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className={cn(
                                        actionButton,
                                        'border border-forest-200 text-forest-900 hover:bg-forest-50',
                                    )}
                                >
                                    <ExternalLink
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                    {t('destinations.openMap')}
                                </a>
                            )}
                            {destination.video_url && (
                                <a
                                    href={destination.video_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className={cn(
                                        actionButton,
                                        'border border-forest-200 text-forest-900 hover:bg-forest-50',
                                    )}
                                >
                                    <Play
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                    {t('destinations.watchVideo')}
                                </a>
                            )}
                        </div>
                    </aside>
                </div>
            </section>

            {related.length > 0 && (
                <section className="bg-cream-dark py-16">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6">
                        <SectionHeading
                            eyebrow={t('destinations.eyebrow')}
                            title={t('destinations.related')}
                        />
                        <div className="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            {related.map((item) => (
                                <DestinationCard
                                    key={item.id}
                                    destination={item}
                                />
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </>
    );
}
