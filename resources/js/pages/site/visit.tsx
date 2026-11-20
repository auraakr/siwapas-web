import { Head, Link, usePage } from '@inertiajs/react';
import { Clock, ExternalLink, MapPin, MessageCircle } from 'lucide-react';
import PageHeader from '@/components/site/page-header';
import { useTranslation, type MessageKey } from '@/lib/i18n';
import { withWhatsappText } from '@/lib/whatsapp';
import facilities from '@/routes/facilities';
import type { PageContent } from '@/types/site';

const steps: { title: MessageKey; text: MessageKey }[] = [
    { title: 'visit.step1.title', text: 'visit.step1.text' },
    { title: 'visit.step2.title', text: 'visit.step2.text' },
    { title: 'visit.step3.title', text: 'visit.step3.text' },
    { title: 'visit.step4.title', text: 'visit.step4.text' },
];

const primaryButton =
    'inline-flex items-center justify-center gap-2 rounded-md bg-clay-dark px-6 py-3 text-xs font-semibold tracking-wider text-white uppercase transition hover:bg-clay focus-visible:ring-2 focus-visible:ring-forest-600 focus-visible:ring-offset-2 focus-visible:outline-none';

export default function Visit({
    info,
    terms,
}: {
    info: PageContent | null;
    terms: PageContent | null;
}) {
    const { site } = usePage().props;
    const { t } = useTranslation();
    const contact = site.contact;
    const hasFacts = Boolean(
        contact.hours || contact.address || contact.maps_url,
    );

    return (
        <>
            <Head title={t('visit.pageTitle')}>
                <meta name="description" content={t('visit.pageDescription')} />
            </Head>

            <PageHeader
                eyebrow={t('nav.visit')}
                title={t('visit.pageTitle')}
                description={t('visit.pageDescription')}
            />

            {(hasFacts || info?.body) && (
                <section className="bg-cream py-14 md:py-20">
                    <div className="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-[1fr_1.4fr]">
                        {hasFacts && (
                            <div className="h-fit space-y-4 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                                {contact.hours && (
                                    <div>
                                        <h3 className="mb-1 flex items-center gap-2 text-sm font-semibold text-forest-900">
                                            <Clock
                                                className="size-4 text-clay-dark"
                                                aria-hidden="true"
                                            />
                                            {t('visit.hoursLabel')}
                                        </h3>
                                        <p className="text-forest-800/90">
                                            {contact.hours}
                                        </p>
                                    </div>
                                )}
                                {contact.address && (
                                    <div>
                                        <h3 className="mb-1 flex items-center gap-2 text-sm font-semibold text-forest-900">
                                            <MapPin
                                                className="size-4 text-clay-dark"
                                                aria-hidden="true"
                                            />
                                            {t('village.location')}
                                        </h3>
                                        <p className="text-forest-800/90">
                                            {contact.address}
                                        </p>
                                    </div>
                                )}
                                {contact.maps_url && (
                                    <a
                                        href={contact.maps_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="inline-flex items-center gap-2 text-sm font-semibold text-clay-dark underline underline-offset-4"
                                    >
                                        <ExternalLink
                                            className="size-4"
                                            aria-hidden="true"
                                        />
                                        {t('visit.map')}
                                    </a>
                                )}
                            </div>
                        )}
                        {info?.body && (
                            <div>
                                <h2 className="mb-4 font-serif text-3xl font-semibold text-forest-900">
                                    {info.title}
                                </h2>
                                <div
                                    className="rich-text"
                                    dangerouslySetInnerHTML={{
                                        __html: info.body,
                                    }}
                                />
                            </div>
                        )}
                    </div>
                </section>
            )}

            <section
                id="cara-reservasi"
                className="scroll-mt-28 bg-cream-dark py-14 md:py-20"
            >
                <div className="mx-auto max-w-6xl px-4 sm:px-6">
                    <h2 className="font-serif text-3xl font-semibold text-forest-900 md:text-4xl">
                        {t('visit.steps')}
                    </h2>
                    <ol className="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {steps.map((step, index) => (
                            <li
                                key={step.title}
                                className="rounded-2xl border border-forest-100 bg-white p-6 shadow-sm"
                            >
                                <span
                                    aria-hidden="true"
                                    className="flex size-9 items-center justify-center rounded-full bg-forest-800 font-semibold text-cream"
                                >
                                    {index + 1}
                                </span>
                                <h3 className="mt-4 font-serif text-lg font-semibold text-forest-900">
                                    {t(step.title)}
                                </h3>
                                <p className="mt-1 text-sm leading-relaxed text-forest-800/80">
                                    {t(step.text)}
                                </p>
                            </li>
                        ))}
                    </ol>
                    <div className="mt-10">
                        <Link
                            href={facilities.index()}
                            className={primaryButton}
                        >
                            {t('visit.seeFacilities')}
                        </Link>
                    </div>
                </div>
            </section>

            {terms?.body && (
                <section
                    id="ketentuan"
                    className="scroll-mt-28 bg-cream py-14 md:py-20"
                >
                    <div className="mx-auto max-w-3xl px-4 sm:px-6">
                        <h2 className="mb-6 font-serif text-3xl font-semibold text-forest-900 md:text-4xl">
                            {terms.title}
                        </h2>
                        <div className="rounded-2xl border border-forest-100 bg-white p-6 shadow-sm md:p-8">
                            <div
                                className="rich-text"
                                dangerouslySetInnerHTML={{ __html: terms.body }}
                            />
                        </div>
                    </div>
                </section>
            )}

            <section className="bg-forest-900 py-14 text-cream">
                <div className="mx-auto flex max-w-6xl flex-col items-start gap-5 px-4 sm:px-6 md:flex-row md:items-center md:justify-between">
                    <h2 className="font-serif text-2xl font-semibold md:text-3xl">
                        {t('visit.needHelp')}
                    </h2>
                    <div className="flex flex-wrap gap-3">
                        {contact.whatsapp_url && (
                            <a
                                href={withWhatsappText(
                                    contact.whatsapp_url,
                                    t('visit.askMessage'),
                                )}
                                target="_blank"
                                rel="noopener noreferrer"
                                className={primaryButton}
                            >
                                <MessageCircle
                                    className="size-4"
                                    aria-hidden="true"
                                />
                                {t('visit.whatsapp')}
                            </a>
                        )}
                        <Link
                            href="/kontak"
                            className="inline-flex items-center justify-center rounded-md border border-cream/50 px-6 py-3 text-xs font-semibold tracking-wider uppercase transition hover:bg-cream/10 focus-visible:ring-2 focus-visible:ring-cream focus-visible:outline-none"
                        >
                            {t('cta.contact')}
                        </Link>
                    </div>
                </div>
            </section>
        </>
    );
}
