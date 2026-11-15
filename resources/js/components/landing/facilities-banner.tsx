import { Link } from '@inertiajs/react';
import { ArrowRight, Users } from 'lucide-react';
import SectionHeading from '@/components/landing/section-heading';
import { formatRupiah } from '@/lib/format';
import { useTranslation } from '@/lib/i18n';
import facilities from '@/routes/facilities';
import type { FacilitySummary } from '@/types/landing';

export default function FacilitiesBanner({
    items,
}: {
    items: FacilitySummary[];
}) {
    const { t } = useTranslation();
    const steps = [
        t('facilities.step1'),
        t('facilities.step2'),
        t('facilities.step3'),
    ];

    return (
        <section
            id="fasilitas"
            className="relative isolate scroll-mt-24 overflow-hidden bg-forest-900 py-20 text-cream"
        >
            <div className="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_20%_20%,rgba(47,122,82,0.5),transparent_55%),radial-gradient(circle_at_85%_80%,rgba(196,98,45,0.25),transparent_50%)]" />
            <div className="mx-auto max-w-6xl px-4 sm:px-6">
                <div className="text-center">
                    <SectionHeading
                        centered
                        inverted
                        eyebrow={t('facilities.eyebrow')}
                        title={t('facilities.title')}
                        description={t('facilities.description')}
                    />
                </div>

                <ol className="mx-auto mt-10 grid max-w-3xl gap-3 sm:grid-cols-3">
                    {steps.map((step, index) => (
                        <li
                            key={step}
                            className="flex items-center gap-3 text-left text-sm"
                        >
                            <span
                                aria-hidden="true"
                                className="flex size-8 shrink-0 items-center justify-center rounded-full bg-brand-yellow font-semibold text-forest-950"
                            >
                                {index + 1}
                            </span>
                            {step}
                        </li>
                    ))}
                </ol>

                {items.length > 0 && (
                    <ul className="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {items.map((facility) => (
                            <li key={facility.slug}>
                                <Link
                                    href={facilities.show(facility.slug)}
                                    className="group flex h-full flex-col rounded-2xl border border-cream/20 bg-forest-950/40 p-6 backdrop-blur transition hover:border-brand-yellow/60 hover:bg-forest-950/60 focus-visible:ring-2 focus-visible:ring-brand-yellow focus-visible:outline-none"
                                >
                                    <h3 className="font-serif text-xl font-semibold">
                                        {facility.name}
                                    </h3>
                                    {facility.capacity !== null && (
                                        <p className="mt-1 flex items-center gap-1.5 text-sm text-forest-200">
                                            <Users
                                                className="size-4"
                                                aria-hidden="true"
                                            />
                                            {t('facilities.capacity', {
                                                count: facility.capacity,
                                            })}
                                        </p>
                                    )}
                                    <p className="mt-auto pt-6">
                                        <span className="block text-xs text-forest-200">
                                            {t('facilities.priceFrom')}
                                        </span>
                                        <span className="font-serif text-2xl font-semibold text-brand-yellow">
                                            {formatRupiah(facility.base_price)}
                                        </span>{' '}
                                        <span className="text-sm text-forest-200">
                                            {t('facilities.perDay')}
                                        </span>
                                    </p>
                                    <span className="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-cream transition group-hover:gap-2.5">
                                        {t('facilities.cta')}
                                        <ArrowRight
                                            className="size-4"
                                            aria-hidden="true"
                                        />
                                    </span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}

                <div className="mt-10 text-center">
                    <Link
                        href={facilities.index()}
                        className="inline-block rounded-md bg-clay-dark px-8 py-3.5 text-xs font-semibold tracking-wider text-white uppercase transition hover:bg-clay focus-visible:ring-2 focus-visible:ring-cream focus-visible:ring-offset-2 focus-visible:ring-offset-forest-900 focus-visible:outline-none"
                    >
                        {t('facilities.all')}
                    </Link>
                </div>
            </div>
        </section>
    );
}
