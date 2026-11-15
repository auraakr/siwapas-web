import { Head } from '@inertiajs/react';
import SectionHeading from '@/components/landing/section-heading';
import DestinationCard from '@/components/site/destination-card';
import PageHeader from '@/components/site/page-header';
import { useTranslation } from '@/lib/i18n';
import type { DestinationCard as DestinationCardData } from '@/types/site';

function CardGrid({ items }: { items: DestinationCardData[] }) {
    return (
        <div className="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {items.map((item) => (
                <DestinationCard key={item.id} destination={item} />
            ))}
        </div>
    );
}

export default function DestinationsIndex({
    destinations,
    activities,
}: {
    destinations: DestinationCardData[];
    activities: DestinationCardData[];
}) {
    const { t } = useTranslation();
    const isEmpty = destinations.length === 0 && activities.length === 0;

    return (
        <>
            <Head title={t('destinations.indexTitle')}>
                <meta
                    name="description"
                    content={t('destinations.indexDescription')}
                />
            </Head>

            <PageHeader
                eyebrow={t('destinations.eyebrow')}
                title={t('destinations.indexTitle')}
                description={t('destinations.indexDescription')}
            />

            {isEmpty && (
                <p className="bg-cream py-20 text-center text-forest-800/80">
                    {t('destinations.empty')}
                </p>
            )}

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
