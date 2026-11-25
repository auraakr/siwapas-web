import { usePage } from '@inertiajs/react';
import { MessageCircle, ShoppingBag } from 'lucide-react';
import { useState } from 'react';
import Media from '@/components/landing/media';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { withWhatsappText } from '@/lib/whatsapp';
import type { Product } from '@/types/landing';

/**
 * The maker's own WhatsApp, or the village contact when the maker has no number yet, with the order
 * message filled in.
 */
function useOrderLink(): (product: Product) => string | null {
    const { site } = usePage().props;
    const { t } = useTranslation();

    return (product) => {
        const base = product.whatsapp_url ?? site.contact.whatsapp_url ?? null;

        return base
            ? withWhatsappText(
                  base,
                  t('products.message', { name: product.name }),
              )
            : null;
    };
}

function OrderButton({
    href,
    className,
}: {
    href: string;
    className?: string;
}) {
    const { t } = useTranslation();

    return (
        <a
            href={href}
            target="_blank"
            rel="noopener noreferrer"
            className={cn(
                'inline-flex items-center justify-center gap-2 rounded-md bg-forest-800 px-4 py-2.5 text-sm font-semibold text-cream transition hover:bg-forest-900 focus-visible:ring-2 focus-visible:ring-clay focus-visible:ring-offset-2 focus-visible:outline-none',
                className,
            )}
        >
            <MessageCircle className="size-4" aria-hidden="true" />
            {t('products.contact')}
        </a>
    );
}

function ProductCard({
    product,
    orderLink,
    onOpen,
}: {
    product: Product;
    orderLink: string | null;
    onOpen: () => void;
}) {
    const { t } = useTranslation();

    return (
        <article className="group relative flex flex-col overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm transition focus-within:ring-2 focus-within:ring-clay focus-within:ring-offset-2 hover:-translate-y-0.5 hover:shadow-md">
            <div className="aspect-[4/3] overflow-hidden">
                <Media
                    src={product.image}
                    alt={product.name}
                    icon={ShoppingBag}
                    className="transition duration-500 group-hover:scale-105"
                />
            </div>
            <div className="flex flex-1 flex-col p-5">
                {product.maker && (
                    <p className="text-[11px] font-semibold tracking-[0.15em] text-clay-dark uppercase">
                        {product.maker}
                    </p>
                )}
                <h3 className="mt-1 font-serif text-lg font-semibold text-forest-900">
                    {/* The title button stretches over the card, so the whole card opens the details. */}
                    <button
                        type="button"
                        onClick={onOpen}
                        aria-label={t('umkm.details', { name: product.name })}
                        className="text-left after:absolute after:inset-0 focus-visible:outline-none"
                    >
                        {product.name}
                    </button>
                </h3>
                {product.description && (
                    <p className="mt-2 mb-5 line-clamp-3 text-sm leading-relaxed text-forest-800/80">
                        {product.description}
                    </p>
                )}
                {orderLink && (
                    <OrderButton
                        href={orderLink}
                        className="relative z-10 mt-auto w-full sm:w-auto sm:self-start"
                    />
                )}
            </div>
        </article>
    );
}

function ProductDialog({
    product,
    orderLink,
    onClose,
}: {
    product: Product | null;
    orderLink: string | null;
    onClose: () => void;
}) {
    const { t } = useTranslation();
    const [photoIndex, setPhotoIndex] = useState(0);
    const photos = product?.images ?? [];
    const photo = photos[photoIndex] ?? product?.image ?? null;

    return (
        <Dialog
            open={product !== null}
            onOpenChange={(open) => {
                if (!open) {
                    onClose();
                    setPhotoIndex(0);
                }
            }}
        >
            {product && (
                <DialogContent className="site-theme max-h-[90vh] gap-0 overflow-y-auto border-forest-100 bg-cream p-0 sm:max-w-2xl [&>button:last-child]:rounded-full [&>button:last-child]:bg-cream [&>button:last-child]:p-1.5 [&>button:last-child]:text-forest-900 [&>button:last-child]:opacity-90 [&>button:last-child]:shadow">
                    <div className="aspect-[4/3] overflow-hidden bg-forest-900 sm:aspect-video">
                        <Media
                            src={photo}
                            alt={product.name}
                            icon={ShoppingBag}
                        />
                    </div>

                    {photos.length > 1 && (
                        <div className="flex gap-2 overflow-x-auto px-5 pt-4 sm:px-6">
                            {photos.map((url, index) => (
                                <button
                                    key={url}
                                    type="button"
                                    onClick={() => setPhotoIndex(index)}
                                    aria-label={t('umkm.photo', {
                                        number: index + 1,
                                        name: product.name,
                                    })}
                                    aria-current={index === photoIndex}
                                    className={cn(
                                        'size-16 shrink-0 overflow-hidden rounded-md border-2 transition',
                                        index === photoIndex
                                            ? 'border-forest-700'
                                            : 'border-transparent opacity-70 hover:opacity-100',
                                    )}
                                >
                                    <img
                                        src={url}
                                        alt=""
                                        className="size-full object-cover"
                                    />
                                </button>
                            ))}
                        </div>
                    )}

                    <div className="p-5 sm:p-6">
                        {product.maker && (
                            <p className="text-xs font-semibold tracking-[0.15em] text-clay-dark uppercase">
                                {t('umkm.maker')} {product.maker}
                            </p>
                        )}
                        <DialogTitle className="mt-1 font-serif text-2xl font-semibold text-forest-900">
                            {product.name}
                        </DialogTitle>
                        <DialogDescription asChild>
                            <div className="mt-3 text-sm leading-relaxed whitespace-pre-line text-forest-800/90">
                                {product.description ?? t('umkm.noDescription')}
                            </div>
                        </DialogDescription>
                        {orderLink && (
                            <OrderButton
                                href={orderLink}
                                className="mt-6 w-full sm:w-auto"
                            />
                        )}
                    </div>
                </DialogContent>
            )}
        </Dialog>
    );
}

/**
 * UMKM product cards; tapping a card opens its details in a dialog, so no separate page is needed.
 */
export default function ProductGrid({
    products,
    className,
}: {
    products: Product[];
    className?: string;
}) {
    const [selected, setSelected] = useState<Product | null>(null);
    const orderLinkFor = useOrderLink();

    return (
        <>
            <div
                className={cn(
                    'grid gap-5 sm:grid-cols-2 lg:grid-cols-3',
                    className,
                )}
            >
                {products.map((product) => (
                    <ProductCard
                        key={product.id}
                        product={product}
                        orderLink={orderLinkFor(product)}
                        onOpen={() => setSelected(product)}
                    />
                ))}
            </div>

            <ProductDialog
                product={selected}
                orderLink={selected ? orderLinkFor(selected) : null}
                onClose={() => setSelected(null)}
            />
        </>
    );
}
