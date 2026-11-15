import { Link } from '@inertiajs/react';
import { MapPin } from 'lucide-react';
import Media from '@/components/landing/media';
import { useTranslation } from '@/lib/i18n';
import destinationRoutes from '@/routes/destinations';
import type { DestinationCard as DestinationCardData } from '@/types/site';

/**
 * One tappable card: the title link stretches over the whole card, so there is a single focus stop.
 */
export default function DestinationCard({
    destination,
}: {
    destination: DestinationCardData;
}) {
    const { t } = useTranslation();

    return (
        <article className="group relative isolate aspect-[4/5] overflow-hidden rounded-2xl bg-forest-900 focus-within:ring-2 focus-within:ring-brand-yellow focus-within:ring-offset-2">
            <Media
                src={destination.image}
                alt={destination.name}
                className="transition duration-500 group-hover:scale-105"
            />
            <div className="absolute inset-0 bg-linear-to-t from-forest-950/85 via-forest-950/10 to-transparent" />
            <div className="absolute inset-x-0 bottom-0 p-5 text-cream">
                {destination.requires_reservation && (
                    <span className="mb-2 inline-block rounded-full bg-brand-yellow px-2.5 py-0.5 text-[11px] font-semibold text-forest-950">
                        {t('activities.needsReservation')}
                    </span>
                )}
                <h3 className="font-serif text-xl font-semibold">
                    <Link
                        href={destinationRoutes.show(destination.slug)}
                        className="after:absolute after:inset-0 focus-visible:outline-none"
                    >
                        {destination.name}
                    </Link>
                </h3>
                {destination.summary && (
                    <p className="mt-1 line-clamp-2 text-sm text-forest-100">
                        {destination.summary}
                    </p>
                )}
                {destination.address && (
                    <p className="mt-2 flex items-center gap-1.5 text-xs text-forest-200">
                        <MapPin
                            className="size-3.5 shrink-0"
                            aria-hidden="true"
                        />
                        {destination.address}
                    </p>
                )}
            </div>
        </article>
    );
}
