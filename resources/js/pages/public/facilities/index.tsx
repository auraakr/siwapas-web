import { Head, Link } from '@inertiajs/react';
import { ArrowRight, Users } from 'lucide-react';
import PageTitle from '@/components/site/page-title';
import { formatRupiah } from '@/lib/format';
import { useTranslation } from '@/lib/i18n';
import { show } from '@/routes/facilities';

type Facility = {
    slug: string;
    name: string;
    description: string | null;
    capacity: number | null;
    base_price: number;
};

export default function FacilitiesIndex({
    facilities,
}: {
    facilities: Facility[];
}) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('nav.facilities')}>
                <meta
                    name="description"
                    content={t('facilities.description')}
                />
            </Head>

            <PageTitle
                eyebrow={t('facilities.eyebrow')}
                title={t('nav.facilities')}
                description={t('facilities.description')}
            />

            {facilities.length === 0 ? (
                <p className="rounded-2xl border bg-white p-8 text-center text-muted-foreground">
                    {t('facilities.none')}
                </p>
            ) : (
                <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {facilities.map((facility) => (
                        <li key={facility.slug}>
                            <Link
                                href={show(facility.slug)}
                                className="group flex h-full flex-col rounded-2xl border bg-white p-6 shadow-sm transition hover:border-forest-600 focus-visible:ring-2 focus-visible:ring-forest-600 focus-visible:outline-none"
                            >
                                <h2 className="font-serif text-xl font-semibold text-forest-900">
                                    {facility.name}
                                </h2>
                                {facility.capacity !== null && (
                                    <p className="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground">
                                        <Users
                                            className="size-4"
                                            aria-hidden="true"
                                        />
                                        {t('facilities.capacity', {
                                            count: facility.capacity,
                                        })}
                                    </p>
                                )}
                                {facility.description && (
                                    <p className="mt-3 line-clamp-3 text-sm leading-relaxed text-forest-800/80">
                                        {facility.description}
                                    </p>
                                )}
                                <div className="mt-auto flex items-end justify-between gap-3 pt-6">
                                    <p>
                                        <span className="block text-xs text-muted-foreground">
                                            {t('facilities.priceFrom')}
                                        </span>
                                        <span className="font-serif text-xl font-semibold text-forest-900">
                                            {formatRupiah(facility.base_price)}
                                        </span>{' '}
                                        <span className="text-sm text-muted-foreground">
                                            {t('facilities.perDay')}
                                        </span>
                                    </p>
                                    <span className="inline-flex items-center gap-1.5 text-sm font-semibold text-clay-dark transition group-hover:gap-2.5">
                                        {t('facilities.cta')}
                                        <ArrowRight
                                            className="size-4"
                                            aria-hidden="true"
                                        />
                                    </span>
                                </div>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </>
    );
}