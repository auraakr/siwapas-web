import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import SectionHeading from '@/components/landing/section-heading';
import ProductGrid from '@/components/site/product-grid';
import { useTranslation } from '@/lib/i18n';
import destinations from '@/routes/destinations';
import type { Product } from '@/types/landing';

/**
 * A preview of the UMKM products; the full list lives on the products tab of /destinasi.
 */
export default function Products({ products }: { products: Product[] }) {
    const { t } = useTranslation();

    if (products.length === 0) {
        return null;
    }

    return (
        <section id="produk" className="scroll-mt-24 bg-cream py-20">
            <div className="mx-auto max-w-6xl px-4">
                <SectionHeading
                    eyebrow={t('umkm.eyebrow')}
                    title={t('umkm.title')}
                    description={t('umkm.description')}
                />

                <ProductGrid
                    products={products}
                    className="mt-10 lg:grid-cols-4"
                />

                <div className="mt-10 text-center">
                    <Link
                        href={destinations.index({ query: { tab: 'umkm' } })}
                        className="inline-flex items-center gap-2 rounded-md border border-forest-800 px-5 py-2.5 text-sm font-semibold text-forest-900 transition hover:bg-forest-800 hover:text-cream"
                    >
                        {t('umkm.viewAll')}
                        <ArrowRight className="size-4" aria-hidden="true" />
                    </Link>
                </div>
            </div>
        </section>
    );
}
