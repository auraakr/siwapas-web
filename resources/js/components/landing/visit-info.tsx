import { Link } from '@inertiajs/react';
import {
    CalendarDays,
    ChevronDown,
    Clock,
    Mail,
    MapPin,
    MessageCircle,
    Phone,
    Store,
    Ticket,
    type LucideIcon,
} from 'lucide-react';
import SectionHeading from '@/components/landing/section-heading';
import { useTranslation } from '@/lib/i18n';
import events from '@/routes/events';
import facilities from '@/routes/facilities';
import type { Contact, Faq, VisitSummary } from '@/types/landing';

function InfoCard({
    icon: Icon,
    title,
    text,
    link,
}: {
    icon: LucideIcon;
    title: string;
    text: string;
    link?: { href: string; label: string };
}) {
    return (
        <li className="flex flex-col rounded-2xl border border-forest-100 bg-white p-5 shadow-sm">
            <span className="flex size-10 items-center justify-center rounded-full bg-forest-100 text-forest-700">
                <Icon className="size-5" aria-hidden="true" />
            </span>
            <h3 className="mt-4 font-semibold text-forest-900">{title}</h3>
            <p className="mt-1 text-sm leading-relaxed text-forest-800/80">
                {text}
            </p>
            {link && (
                <Link
                    href={link.href}
                    className="mt-auto pt-3 text-sm font-semibold text-clay-dark underline underline-offset-4"
                >
                    {link.label}
                </Link>
            )}
        </li>
    );
}

/**
 * The full visit information: hours, admission, rentals and event tickets, then how to reach the
 * village and the frequently asked questions. Reads the same settings as the strip under the hero.
 */
export default function VisitInfo({
    visit,
    contact,
    faqs,
}: {
    visit: VisitSummary;
    contact: Contact;
    faqs: Faq[];
}) {
    const { t } = useTranslation();
    const phoneHref = contact.phone
        ? `tel:${contact.phone.replace(/[^\d+]/g, '')}`
        : undefined;
    const hasContact = Boolean(
        contact.address ||
        contact.maps_url ||
        contact.whatsapp_url ||
        contact.email ||
        contact.phone,
    );

    return (
        <section id="kunjungan" className="scroll-mt-24 bg-cream-dark py-20">
            <div className="mx-auto max-w-6xl px-4 sm:px-6">
                <SectionHeading
                    centered
                    eyebrow={t('visit.eyebrow')}
                    title={t('visit.title')}
                />

                <ul className="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <InfoCard
                        icon={Clock}
                        title={t('visitInfo.hours')}
                        text={visit.hours ?? t('visitInfo.hoursUnknown')}
                    />
                    <InfoCard
                        icon={Ticket}
                        title={t('visitInfo.admission')}
                        text={visit.admission}
                    />
                    <InfoCard
                        icon={Store}
                        title={t('visitInfo.rental')}
                        text={t('visitInfo.rentalText')}
                        link={{
                            href: facilities.index().url,
                            label: t('visitInfo.rentalLink'),
                        }}
                    />
                    <InfoCard
                        icon={CalendarDays}
                        title={t('visitInfo.events')}
                        text={t('visitInfo.eventsText')}
                        link={{
                            href: events.index().url,
                            label: t('visitInfo.eventsLink'),
                        }}
                    />
                </ul>

                <div className="mt-10 grid gap-8 lg:grid-cols-2">
                    {hasContact && (
                        <div className="rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                            <h3 className="font-serif text-xl font-semibold text-forest-900">
                                {t('visitInfo.reachUs')}
                            </h3>
                            <ul className="mt-4 space-y-3 text-sm text-forest-800">
                                {contact.address && (
                                    <li className="flex gap-2">
                                        <MapPin
                                            className="mt-0.5 size-4 shrink-0 text-clay-dark"
                                            aria-hidden="true"
                                        />
                                        {contact.address}
                                    </li>
                                )}
                                {contact.whatsapp && contact.whatsapp_url && (
                                    <li className="flex gap-2">
                                        <MessageCircle
                                            className="mt-0.5 size-4 shrink-0 text-clay-dark"
                                            aria-hidden="true"
                                        />
                                        <a
                                            href={contact.whatsapp_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="hover:underline"
                                        >
                                            WhatsApp {contact.whatsapp}
                                        </a>
                                    </li>
                                )}
                                {contact.phone && (
                                    <li className="flex gap-2">
                                        <Phone
                                            className="mt-0.5 size-4 shrink-0 text-clay-dark"
                                            aria-hidden="true"
                                        />
                                        <a
                                            href={phoneHref}
                                            className="hover:underline"
                                        >
                                            {contact.phone}
                                        </a>
                                    </li>
                                )}
                                {contact.email && (
                                    <li className="flex gap-2">
                                        <Mail
                                            className="mt-0.5 size-4 shrink-0 text-clay-dark"
                                            aria-hidden="true"
                                        />
                                        <a
                                            href={`mailto:${contact.email}`}
                                            className="break-all hover:underline"
                                        >
                                            {contact.email}
                                        </a>
                                    </li>
                                )}
                            </ul>
                            <div className="flex flex-wrap gap-3 pt-5">
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
