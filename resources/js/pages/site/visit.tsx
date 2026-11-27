import { Head, Link, usePage } from '@inertiajs/react';
import {
    Clock,
    ExternalLink,
    Info,
    MapPin,
    MessageCircle,
    Users,
} from 'lucide-react';
import Media from '@/components/landing/media';
import PageHeader from '@/components/site/page-header';
import { formatRupiah } from '@/lib/format';
import { useTranslation, type MessageKey } from '@/lib/i18n';
import { withWhatsappText } from '@/lib/whatsapp';
import facilitiesRoutes from '@/routes/facilities';
import type { PageContent } from '@/types/site';

type VisitFacility = {
    slug: string;
    name: string;
    summary: string | null;
    image: string | null;
    base_price: number;
    unit: string;
    capacity: number | null;
};

const steps: { title: MessageKey; text: MessageKey }[] = [
    { title: 'visit.flow.choose.title', text: 'visit.flow.choose.text' },
    { title: 'visit.flow.form.title', text: 'visit.flow.form.text' },
    { title: 'visit.flow.pay.title', text: 'visit.flow.pay.text' },
    { title: 'visit.flow.verify.title', text: 'visit.flow.verify.text' },
    { title: 'visit.flow.confirmed.title', text: 'visit.flow.confirmed.text' },
    { title: 'visit.flow.settle.title', text: 'visit.flow.settle.text' },
];

const unitKeys: Record<string, MessageKey> = { day: 'visit.perDay' };

function FacilityCard({ facility }: { facility: VisitFacility }) {
    const { t } = useTranslation();

    return (
        <li className="flex flex-col overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm">
            <div className="aspect-[4/3] overflow-hidden">
                <Media src={facility.image} alt={facility.name} />
            </div>
            <div className="flex flex-1 flex-col p-5">
                <h3 className="font-serif text-xl font-semibold text-forest-900">
                    {facility.name}
                </h3>
                {facility.capacity !== null && (
                    <p className="mt-1 flex items-center gap-1.5 text-xs text-forest-700">
                        <Users className="size-3.5" aria-hidden="true" />
                        {t('facilities.capacity', {
                            count: facility.capacity,
                        })}
                    </p>
                )}
                {facility.summary && (
                    <p className="mt-3 line-clamp-3 text-sm leading-relaxed text-forest-800/80">
                        {facility.summary}
                    </p>
                )}
                <div className="mt-auto flex flex-wrap items-end justify-between gap-3 pt-5">
                    <p>
                        <span className="block text-xs text-muted-foreground">
                            {t('facilities.priceFrom')}
                        </span>
                        <span className="font-serif text-xl font-semibold text-forest-900">
                            {formatRupiah(facility.base_price)}
                        </span>
                        <span className="ml-1 text-sm text-muted-foreground">
                            {t(unitKeys[facility.unit] ?? 'visit.perDay')}
                        </span>
                    </p>
                    <Link
                        href={facilitiesRoutes.show(facility.slug)}
                        className="rounded-md bg-clay-dark px-4 py-2 text-sm font-semibold text-white transition hover:bg-clay focus-visible:ring-2 focus-visible:ring-clay focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        {t('visit.reserve')}
                    </Link>
                </div>
            </div>
        </li>
    );
}

export default function Visit({
    info,
    terms,
    facilities,
    dp_percentage,
}: {
    info: PageContent | null;
    terms: PageContent | null;
    facilities: VisitFacility[];
    dp_percentage: number;
}) {
    const { site } = usePage().props;
    const { t } = useTranslation();
    const contact = site.contact;
    const hasFacts = Boolean(
        contact.hours || contact.address || contact.maps_url,
    );
    const whatsapp = contact.whatsapp_url
        ? withWhatsappText(contact.whatsapp_url, t('visit.askMessage'))
        : null;

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
                <section className="bg-cream py-12 md:py-16">
                    <div className="mx-auto grid max-w-6xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_1.4fr]">
                        {hasFacts && (
                            <div className="h-fit space-y-4 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                                {contact.hours && (
                                    <p className="flex gap-2 text-forest-900">
                                        <Clock
                                            className="mt-0.5 size-4 shrink-0 text-clay-dark"
                                            aria-hidden="true"
                                        />
                                        <span>
                                            <span className="block text-xs font-semibold text-forest-700">
                                                {t('visit.hoursLabel')}
                                            </span>
                                            {contact.hours}
                                        </span>
                                    </p>
                                )}
                                {contact.address && (
                                    <p className="flex gap-2 text-forest-900">
                                        <MapPin
                                            className="mt-0.5 size-4 shrink-0 text-clay-dark"
                                            aria-hidden="true"
                                        />
                                        {contact.address}
                                    </p>
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
                                <h2 className="font-serif text-2xl font-semibold text-forest-900">
                                    {info.title}
                                </h2>
                                <div
                                    className="rich-text mt-4"
                                    dangerouslySetInnerHTML={{
                                        __html: info.body,
                                    }}
                                />
                            </div>
                        )}
                    </div>
                </section>
            )}

            {facilities.length > 0 && (
                <section
                    id="fasilitas"
                    className="scroll-mt-24 bg-cream-dark py-14 md:py-20"
                >
                    <div className="mx-auto max-w-6xl px-4 sm:px-6">
                        <h2 className="font-serif text-3xl font-semibold text-forest-900">
                            {t('visit.facilitiesTitle')}
                        </h2>
                        <p className="mt-2 max-w-2xl text-forest-800/80">
                            {t('visit.facilitiesDescription')}
                        </p>
                        <ul className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            {facilities.map((facility) => (
                                <FacilityCard
                                    key={facility.slug}
                                    facility={facility}
                                />
                            ))}
                        </ul>
                    </div>
                </section>
            )}

            <section
                id="tata-cara"
                className="scroll-mt-24 bg-cream py-14 md:py-20"
            >
                <div className="mx-auto max-w-6xl px-4 sm:px-6">
                    <h2 className="font-serif text-3xl font-semibold text-forest-900">
                        {t('visit.steps')}
                    </h2>
                    <ol className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {steps.map((step, index) => (
                            <li
                                key={step.title}
                                className="relative rounded-2xl border border-forest-100 bg-white p-5 pl-16 shadow-sm"
                            >
                                <span
                                    aria-hidden="true"
                                    className="absolute top-5 left-5 flex size-8 items-center justify-center rounded-full bg-forest-800 font-semibold text-cream"
                                >
                                    {index + 1}
                                </span>
                                <h3 className="font-semibold text-forest-900">
                                    {t(step.title, { percent: dp_percentage })}
                                </h3>
                                <p className="mt-1 text-sm leading-relaxed text-forest-800/80">
                                    {t(step.text, { percent: dp_percentage })}
                                </p>
                            </li>
                        ))}
                    </ol>

                    <div
                        role="note"
                        className="mt-8 flex gap-3 rounded-2xl border border-clay/30 bg-clay/10 p-5 text-forest-900"
                    >
                        <Info
                            className="mt-0.5 size-5 shrink-0 text-clay-dark"
                            aria-hidden="true"
                        />
                        <div>
                            <p className="font-semibold">
                                {t('visit.shortNoticeTitle')}
                            </p>
                            <p className="mt-1 text-sm text-forest-800/90">
                                {contact.whatsapp
                                    ? t('visit.shortNoticeText', {
                                          phone: contact.whatsapp,
                                      })
                                    : t('visit.shortNoticeTextNoPhone')}
                            </p>
                            {whatsapp && (
                                <a
                                    href={whatsapp}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="mt-3 inline-flex items-center gap-2 rounded-md bg-forest-800 px-4 py-2 text-sm font-semibold text-cream transition hover:bg-forest-900"
                                >
                                    <MessageCircle
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                    {t('visit.chatManagers')}
                                </a>
                            )}
                        </div>
                    </div>
                </div>
            </section>

            {terms?.body && (
                <section
                    id="ketentuan"
                    className="scroll-mt-24 bg-cream-dark py-14 md:py-20"
                >
                    <div className="mx-auto max-w-3xl px-4 sm:px-6">
                        <h2 className="font-serif text-3xl font-semibold text-forest-900">
                            {t('visit.termsTitle')}
                        </h2>
                        <div
                            className="rich-text mt-6"
                            dangerouslySetInnerHTML={{ __html: terms.body }}
                        />
                    </div>
                </section>
            )}
        </>
    );
}
