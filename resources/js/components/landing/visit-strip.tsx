import { Link } from '@inertiajs/react';
import { CalendarCheck, Clock, Ticket } from 'lucide-react';
import { useTranslation } from '@/lib/i18n';
import facilities from '@/routes/facilities';
import type { VisitSummary } from '@/types/landing';

/**
 * The visit essentials right under the hero: opening hours, admission and a booking button.
 * The full details sit further down in the Visit Information section.
 */
export default function VisitStrip({ visit }: { visit: VisitSummary }) {
    const { t } = useTranslation();

    return (
        <section
            aria-label={t('visit.eyebrow')}
            className="relative z-10 -mt-10 px-4 sm:-mt-12"
        >
            <div className="mx-auto grid max-w-5xl gap-px overflow-hidden rounded-2xl border border-forest-100 bg-forest-100 shadow-lg sm:grid-cols-[1fr_1fr_auto]">
                {visit.hours && (
                    <div className="flex items-center gap-3 bg-white px-5 py-4">
                        <span className="flex size-10 shrink-0 items-center justify-center rounded-full bg-forest-100 text-forest-700">
                            <Clock className="size-5" aria-hidden="true" />
                        </span>
                        <div>
                            <p className="text-[11px] font-semibold tracking-[0.15em] text-clay-dark uppercase">
                                {t('visitInfo.hours')}
                            </p>
                            <p className="font-semibold text-forest-900">
                                {visit.hours}
                            </p>
                        </div>
                    </div>
                )}
                <div className="flex items-center gap-3 bg-white px-5 py-4">
                    <span className="flex size-10 shrink-0 items-center justify-center rounded-full bg-forest-100 text-forest-700">
                        <Ticket className="size-5" aria-hidden="true" />
                    </span>
                    <div>
                        <p className="text-[11px] font-semibold tracking-[0.15em] text-clay-dark uppercase">
                            {t('visitInfo.admission')}
                        </p>
                        <p className="font-semibold text-forest-900">
                            {visit.admission}
                        </p>
                    </div>
                </div>
                <div className="flex items-center bg-white px-5 py-4">
                    <Link
                        href={facilities.index()}
                        className="inline-flex w-full items-center justify-center gap-2 rounded-md bg-clay-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-clay focus-visible:ring-2 focus-visible:ring-clay focus-visible:ring-offset-2 focus-visible:outline-none sm:w-auto"
                    >
                        <CalendarCheck className="size-4" aria-hidden="true" />
                        {t('visitInfo.bookFacility')}
                    </Link>
                </div>
            </div>
        </section>
    );
}
