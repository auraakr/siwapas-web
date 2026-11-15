import { Link } from '@inertiajs/react';
import { ChevronDown, Clock, MapPin } from 'lucide-react';
import SectionHeading from '@/components/landing/section-heading';
import { useTranslation } from '@/lib/i18n';
import type { Contact, Faq } from '@/types/landing';

export default function VisitInfo({
    contact,
    faqs,
}: {
    contact: Contact;
    faqs: Faq[];
}) {
    const { t } = useTranslation();
    const hasContact = Boolean(
        contact.address || contact.hours || contact.maps_url,
    );

    if (!hasContact && faqs.length === 0) {
        return null;
    }

    const mapPlaceholder = (
        <div className="flex aspect-[16/9] flex-col items-center justify-center gap-2 bg-linear-to-br from-forest-100 to-forest-200 text-forest-700 transition group-hover:from-forest-200 group-hover:to-forest-200">
            <MapPin className="size-14" aria-hidden="true" />
            {contact.maps_url && (
                <span className="text-xs font-semibold tracking-wider uppercase">
                    {t('visit.map')}
                </span>
            )}
        </div>
    );

    return (
        <section id="kunjungan" className="scroll-mt-24 bg-cream-dark py-20">
            <div className="mx-auto max-w-6xl px-4 sm:px-6">
                <SectionHeading
                    centered
                    eyebrow={t('visit.eyebrow')}
                    title={t('visit.title')}
                />

                <div className="mt-12 grid gap-8 lg:grid-cols-2">
                    {hasContact && (
                        <div className="overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm">
                            {contact.maps_url ? (
                                <a
                                    href={contact.maps_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label={t('visit.map')}
                                    className="group block focus-visible:ring-2 focus-visible:ring-forest-600 focus-visible:outline-none focus-visible:ring-inset"
                                >
                                    {mapPlaceholder}
                                </a>
                            ) : (
                                mapPlaceholder
                            )}
                            <div className="space-y-3 p-6 text-sm text-forest-800">
                                {contact.address && (
                                    <p className="flex gap-2">
                                        <MapPin
                                            className="mt-0.5 size-4 shrink-0 text-clay-dark"
                                            aria-hidden="true"
                                        />
                                        {contact.address}
                                    </p>
                                )}
                                {contact.hours && (
                                    <p className="flex gap-2">
                                        <Clock
                                            className="mt-0.5 size-4 shrink-0 text-clay-dark"
                                            aria-hidden="true"
                                        />
                                        {contact.hours}
                                    </p>
                                )}
                                <div className="flex flex-wrap gap-3 pt-2">
                                    {contact.maps_url && (
                                        <a
                                            href={contact.maps_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="rounded-md bg-forest-800 px-4 py-2 text-xs font-semibold tracking-wide text-cream uppercase transition hover:bg-forest-900"
                                        >
                                            {t('visit.map')}
                                        </a>
                                    )}
                                    {contact.whatsapp_url && (
                                        <a
                                            href={contact.whatsapp_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="rounded-md border border-forest-800 px-4 py-2 text-xs font-semibold tracking-wide text-forest-900 uppercase transition hover:bg-forest-800 hover:text-cream"
                                        >
                                            {t('visit.whatsapp')}
                                        </a>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}

                    {faqs.length > 0 && (
                        <div
                            className={`space-y-3 ${hasContact ? '' : 'mx-auto w-full max-w-3xl lg:col-span-2'}`}
                        >
                            {faqs.map((faq) => (
                                <details
                                    key={faq.question}
                                    className="group rounded-xl border border-forest-100 bg-white p-5 shadow-sm"
                                >
                                    <summary className="flex cursor-pointer list-none items-center justify-between gap-4 font-medium text-forest-900">
                                        {faq.question}
                                        <ChevronDown
                                            className="size-5 shrink-0 text-clay-dark transition group-open:rotate-180"
                                            aria-hidden="true"
                                        />
                                    </summary>
                                    <p className="mt-3 text-sm leading-relaxed text-forest-800/80">
                                        {faq.answer}
                                    </p>
                                </details>
                            ))}
                            <p className="pt-2 text-sm text-forest-800/80">
                                {t('visit.moreQuestions')}{' '}
                                <Link
                                    href="/kontak"
                                    className="font-semibold text-clay-dark underline underline-offset-4"
                                >
                                    {t('cta.contact')}
                                </Link>
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
}
