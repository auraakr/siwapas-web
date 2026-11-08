import { ChevronDown, Clock, MapPin } from 'lucide-react';
import SectionHeading from '@/components/landing/section-heading';
import type { Contact, Faq } from '@/types/landing';

export default function VisitInfo({
    contact,
    faqs,
}: {
    contact: Contact;
    faqs: Faq[];
}) {
    const hasContact = Boolean(
        contact.address || contact.hours || contact.maps_url,
    );

    if (!hasContact && faqs.length === 0) {
        return null;
    }

    return (
        <section id="kunjungan" className="scroll-mt-24 bg-cream-dark py-20">
            <div className="mx-auto max-w-6xl px-4">
                <SectionHeading
                    centered
                    eyebrow="Informasi Kunjungan"
                    title="Informasi Kunjungan & Tanya Jawab"
                />

                <div className="mt-12 grid gap-8 lg:grid-cols-2">
                    {hasContact && (
                        <div className="overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm">
                            <div className="flex aspect-[16/9] items-center justify-center bg-linear-to-br from-forest-100 to-forest-200 text-forest-700">
                                <MapPin className="size-14" />
                            </div>
                            <div className="space-y-3 p-6 text-sm text-forest-800">
                                {contact.address && (
                                    <p className="flex gap-2">
                                        <MapPin className="mt-0.5 size-4 shrink-0 text-clay" />
                                        {contact.address}
                                    </p>
                                )}
                                {contact.hours && (
                                    <p className="flex gap-2">
                                        <Clock className="mt-0.5 size-4 shrink-0 text-clay" />
                                        {contact.hours}
                                    </p>
                                )}
                                <div className="flex flex-wrap gap-3 pt-2">
                                    {contact.maps_url && (
                                        <a
                                            href={contact.maps_url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="rounded-md bg-forest-800 px-4 py-2 text-xs font-semibold tracking-wide text-cream uppercase transition hover:bg-forest-900"
                                        >
                                            Buka di Peta
                                        </a>
                                    )}
                                    {contact.whatsapp_url && (
                                        <a
                                            href={contact.whatsapp_url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="rounded-md border border-forest-800 px-4 py-2 text-xs font-semibold tracking-wide text-forest-900 uppercase transition hover:bg-forest-800 hover:text-cream"
                                        >
                                            Hubungi WhatsApp
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
                                        <ChevronDown className="size-5 shrink-0 text-clay transition group-open:rotate-180" />
                                    </summary>
                                    <p className="mt-3 text-sm leading-relaxed text-forest-800/80">
                                        {faq.answer}
                                    </p>
                                </details>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
}
