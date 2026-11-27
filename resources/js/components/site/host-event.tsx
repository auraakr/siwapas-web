import { Link, usePage } from '@inertiajs/react';
import { ArrowRight, MessageCircle, PartyPopper } from 'lucide-react';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { withWhatsappText } from '@/lib/whatsapp';
import facilities from '@/routes/facilities';
import type { EventVenue } from '@/types/landing';

/**
 * For visitors who want to hold their own event (community gathering, family day, performance):
 * a WhatsApp link to the managers and the venues they suggest. Shown on the home and events pages.
 */
export default function HostEvent({
    venues,
    className,
}: {
    venues: EventVenue[];
    className?: string;
}) {
    const { t } = useTranslation();
    const { site } = usePage().props;
    const whatsapp = site.contact.whatsapp_url
        ? withWhatsappText(site.contact.whatsapp_url, t('hostEvent.message'))
        : null;

    return (
        <section
            id="acara-sendiri"
            className={cn(
                'scroll-mt-24 bg-forest-900 py-16 text-cream',
                className,
            )}
        >
            <div className="mx-auto grid max-w-6xl gap-8 px-4 md:grid-cols-[1.3fr_1fr] md:items-center">
                <div>
                    <p className="flex items-center gap-2 text-xs font-semibold tracking-[0.2em] text-brand-yellow uppercase">
                        <PartyPopper className="size-4" aria-hidden="true" />
                        {t('hostEvent.eyebrow')}
                    </p>
                    <h2 className="mt-3 font-serif text-3xl font-semibold sm:text-4xl">
                        {t('hostEvent.title')}
                    </h2>
                    <p className="mt-4 max-w-xl leading-relaxed text-forest-100">
                        {t('hostEvent.description')}
                    </p>
                    {whatsapp && (
                        <a
                            href={whatsapp}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="mt-6 inline-flex items-center gap-2 rounded-md bg-clay-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-clay focus-visible:ring-2 focus-visible:ring-cream focus-visible:ring-offset-2 focus-visible:ring-offset-forest-900 focus-visible:outline-none"
                        >
                            <MessageCircle
                                className="size-4"
                                aria-hidden="true"
                            />
                            {t('hostEvent.contact')}
                        </a>
                    )}
                </div>

                <div className="rounded-2xl bg-forest-800/60 p-5">
                    <p className="text-sm font-semibold">
                        {t('hostEvent.venues')}
                    </p>
                    <ul className="mt-3 space-y-2">
                        {venues.map((venue) => (
                            <li key={venue.slug}>
                                <Link
                                    href={facilities.show(venue.slug)}
                                    className="flex items-center justify-between gap-3 rounded-lg bg-forest-900/60 px-4 py-3 text-sm font-medium transition hover:bg-forest-950"
                                >
                                    {venue.name}
                                    <ArrowRight
                                        className="size-4 shrink-0"
                                        aria-hidden="true"
                                    />
                                </Link>
                            </li>
                        ))}
                        <li>
                            <Link
                                href={facilities.index()}
                                className="flex items-center justify-between gap-3 rounded-lg border border-cream/30 px-4 py-3 text-sm font-medium transition hover:bg-forest-950"
                            >
                                {t('hostEvent.allFacilities')}
                                <ArrowRight
                                    className="size-4 shrink-0"
                                    aria-hidden="true"
                                />
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
    );
}
