import Media from '@/components/landing/media';
import SectionHeading from '@/components/landing/section-heading';
import type { Product } from '@/types/landing';

export default function Products({ products }: { products: Product[] }) {
    if (products.length === 0) {
        return null;
    }

    return (
        <section id="produk" className="scroll-mt-24 bg-cream py-20">
            <div className="mx-auto max-w-6xl px-4">
                <SectionHeading
                    eyebrow="Karya Warga Desa"
                    title="Produk Pilihan Desa"
                    description="Oleh-oleh dan karya UMKM warga. Hubungi pembuatnya langsung untuk memesan."
                />

                <div className="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    {products.map((product) => (
                        <article
                            key={product.id}
                            className="flex flex-col overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm"
                        >
                            <div className="aspect-[4/3]">
                                <Media src={product.image} alt={product.name} />
                            </div>
                            <div className="flex flex-1 flex-col p-5">
                                {product.maker && (
                                    <p className="text-[11px] font-semibold tracking-[0.2em] text-clay uppercase">
                                        {product.maker}
                                    </p>
                                )}
                                <h3 className="mt-1 font-serif text-lg font-semibold text-forest-900">
                                    {product.name}
                                </h3>
                                {product.description && (
                                    <p className="mt-2 line-clamp-3 text-sm leading-relaxed text-forest-800/80">
                                        {product.description}
                                    </p>
                                )}
                                {product.whatsapp_url && (
                                    <a
                                        href={`${product.whatsapp_url}?text=${encodeURIComponent(`Halo, saya tertarik dengan ${product.name}.`)}`}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="mt-4 inline-block self-start rounded-md border border-forest-800 px-4 py-2 text-xs font-semibold tracking-wide text-forest-900 uppercase transition hover:bg-forest-800 hover:text-cream"
                                    >
                                        Hubungi via WhatsApp
                                    </a>
                                )}
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}
