import { Head, router } from '@inertiajs/react';
import { Compass, ShoppingBag, type LucideIcon } from 'lucide-react';
import { useEffect, useRef, type KeyboardEvent } from 'react';
import SectionHeading from '@/components/landing/section-heading';
import DestinationCard from '@/components/site/destination-card';
import PageHeader from '@/components/site/page-header';
import ProductGrid from '@/components/site/product-grid';
import { useTranslation, type MessageKey } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import destinationRoutes from '@/routes/destinations';
import type { Product } from '@/types/landing';
import type { DestinationCard as DestinationCardData } from '@/types/site';

type Tab = 'destinasi' | 'umkm';

const tabs: { id: Tab; label: MessageKey; icon: LucideIcon }[] = [
    { id: 'destinasi', label: 'umkm.tabDestinations', icon: Compass },
    { id: 'umkm', label: 'umkm.tabProducts', icon: ShoppingBag },
];

function CardGrid({ items }: { items: DestinationCardData[] }) {
    return (
        <div className="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {items.map((item) => (
                <DestinationCard key={item.id} destination={item} />
            ))}
        </div>
    );
}

function DestinationsPanel({
    destinations,
    activities,
}: {
    destinations: DestinationCardData[];
    activities: DestinationCardData[];
}) {
    const { t } = useTranslation();

    if (destinations.length === 0 && activities.length === 0) {
        return (
            <p className="bg-cream py-20 text-center text-forest-800/80">
                {t('destinations.empty')}
            </p>
        );
    }

    return (
        <>
            {destinations.length > 0 && (
                <section
                    id="destinasi"
                    className="scroll-mt-24 bg-cream py-16 md:py-20"
                >
                    <div className="mx-auto max-w-6xl px-4 sm:px-6">
                        <SectionHeading
                            eyebrow={t('destinations.eyebrow')}
                            title={t('destinations.title')}
                            description={t('destinations.description')}
                        />
                        <CardGrid items={destinations} />
                    </div>
                </section>
            )}

            {activities.length > 0 && (
                <section
                    id="pengalaman"
                    className="scroll-mt-24 bg-cream-dark py-16 md:py-20"
                >
                    <div className="mx-auto max-w-6xl px-4 sm:px-6">
                        <SectionHeading
                            eyebrow={t('activities.eyebrow')}
                            title={t('activities.title')}
                            description={t('activities.description')}
                        />
                        <CardGrid items={activities} />
                    </div>
                </section>
            )}
        </>
    );
}

function ProductsPanel({ products }: { products: Product[] }) {
    const { t } = useTranslation();

    return (
        <section id="umkm" className="scroll-mt-24 bg-cream py-16 md:py-20">
            <div className="mx-auto max-w-6xl px-4 sm:px-6">
                <SectionHeading
                    eyebrow={t('umkm.eyebrow')}
                    title={t('umkm.title')}
                    description={t('umkm.description')}
                />

                {products.length > 0 ? (
                    <ProductGrid products={products} className="mt-10" />
                ) : (
                    <div className="mt-10 rounded-2xl border border-dashed border-forest-200 bg-white/60 px-6 py-14 text-center">
                        <span className="mx-auto flex size-14 items-center justify-center rounded-full bg-forest-100 text-forest-700">
                            <ShoppingBag
                                className="size-6"
                                aria-hidden="true"
                            />
                        </span>
                        <h3 className="mt-4 font-serif text-xl font-semibold text-forest-900">
                            {t('umkm.emptyTitle')}
                        </h3>
                        <p className="mx-auto mt-2 max-w-md text-sm text-forest-800/80">
                            {t('umkm.emptyText')}
                        </p>
                    </div>
                )}
            </div>
        </section>
    );
}

export default function DestinationsIndex({
    tab,
    destinations,
    activities,
    products,
}: {
    tab: Tab;
    destinations: DestinationCardData[];
    activities: DestinationCardData[];
    products: Product[];
}) {
    const { t } = useTranslation();
    const tabRefs = useRef<Record<Tab, HTMLButtonElement | null>>({
        destinasi: null,
        umkm: null,
    });

    const urlFor = (next: Tab): string =>
        destinationRoutes.index(
            next === 'umkm' ? { query: { tab: 'umkm' } } : undefined,
        ).url;

    // Switching tabs only rewrites the URL (?tab=umkm), so the tab can be shared without a reload.
    const selectTab = (next: Tab) => {
        if (next === tab) {
            return;
        }

        router.replace({
            url: urlFor(next),
            props: (props) => ({ ...props, tab: next }),
            preserveScroll: true,
            preserveState: true,
        });
    };

    // /destinasi#umkm opens the products tab too; the hash never reaches the server. The new URL
    // drops the hash, so this runs only once.
    useEffect(() => {
        if (window.location.hash === '#umkm' && tab !== 'umkm') {
            router.replace({
                url: urlFor('umkm'),
                props: (props) => ({ ...props, tab: 'umkm' }),
                preserveScroll: true,
                preserveState: true,
            });
        }
    });

    const onTabKeyDown = (event: KeyboardEvent<HTMLButtonElement>) => {
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
            return;
        }

        event.preventDefault();
        const next: Tab = tab === 'destinasi' ? 'umkm' : 'destinasi';
        selectTab(next);
        tabRefs.current[next]?.focus();
    };

    const title =
        tab === 'umkm' ? t('umkm.title') : t('destinations.indexTitle');

    return (
        <>
            <Head title={t('destinations.indexTitle')}>
                <meta
                    name="description"
                    content={
                        tab === 'umkm'
                            ? t('umkm.description')
                            : t('destinations.indexDescription')
                    }
                />
            </Head>

            <PageHeader
                eyebrow={t('destinations.eyebrow')}
                title={t('destinations.indexTitle')}
                description={t('destinations.indexDescription')}
            />

            <div className="border-b border-forest-100 bg-cream">
                <div
                    role="tablist"
                    aria-label={title}
                    className="mx-auto flex max-w-6xl gap-1 px-4 sm:px-6"
                >
                    {tabs.map(({ id, label, icon: Icon }) => {
                        const isActive = tab === id;

                        return (
                            <button
                                key={id}
                                ref={(element) => {
                                    tabRefs.current[id] = element;
                                }}
                                type="button"
                                role="tab"
                                id={`tab-${id}`}
                                aria-selected={isActive}
                                aria-controls={`panel-${id}`}
                                tabIndex={isActive ? 0 : -1}
                                onClick={() => selectTab(id)}
                                onKeyDown={onTabKeyDown}
                                className={cn(
                                    '-mb-px flex flex-1 items-center justify-center gap-2 border-b-2 px-2 py-4 text-sm font-semibold whitespace-nowrap transition sm:flex-none sm:px-6',
                                    isActive
                                        ? 'border-clay text-forest-900'
                                        : 'border-transparent text-forest-700/80 hover:text-forest-900',
                                )}
                            >
                                <Icon className="size-4" aria-hidden="true" />
                                {t(label)}
                                {id === 'umkm' && products.length > 0 && (
                                    <span
                                        className={cn(
                                            'rounded-full px-2 py-0.5 text-[11px]',
                                            isActive
                                                ? 'bg-clay text-cream'
                                                : 'bg-forest-100 text-forest-800',
                                        )}
                                    >
                                        {products.length}
                                    </span>
                                )}
                            </button>
                        );
                    })}
                </div>
            </div>

            <div
                role="tabpanel"
                id={`panel-${tab}`}
                aria-labelledby={`tab-${tab}`}
            >
                {tab === 'umkm' ? (
                    <ProductsPanel products={products} />
                ) : (
                    <DestinationsPanel
                        destinations={destinations}
                        activities={activities}
                    />
                )}
            </div>
        </>
    );
}
