import { Head } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, Play, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import PageHeader from '@/components/site/page-header';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import type { GalleryEntry } from '@/types/site';

const lightboxButton =
    'pointer-events-auto absolute rounded-full bg-cream/15 p-3 text-cream transition hover:bg-cream/30 focus-visible:ring-2 focus-visible:ring-brand-yellow focus-visible:outline-none';

export default function Gallery({
    items,
    categories,
}: {
    items: GalleryEntry[];
    categories: string[];
}) {
    const { t } = useTranslation();
    const [category, setCategory] = useState<string | null>(null);
    const [active, setActive] = useState<number | null>(null);
    const dialogRef = useRef<HTMLDialogElement>(null);

    const visible =
        category === null
            ? items
            : items.filter((item) => item.category === category);
    const current = active === null ? undefined : visible[active];
    const isOpen = active !== null;

    // The native <dialog> gives us the focus trap, Escape key and backdrop for free.
    useEffect(() => {
        const dialog = dialogRef.current;

        if (!dialog) {
            return;
        }

        if (isOpen && !dialog.open) {
            dialog.showModal();
        }

        if (!isOpen && dialog.open) {
            dialog.close();
        }

        document.body.style.overflow = isOpen ? 'hidden' : '';

        return () => {
            document.body.style.overflow = '';
        };
    }, [isOpen]);

    const move = (step: number) => {
        if (active === null || visible.length === 0) {
            return;
        }

        setActive((active + step + visible.length) % visible.length);
    };

    return (
        <>
            <Head title={t('gallery.eyebrow')}>
                <meta name="description" content={t('gallery.description')} />
            </Head>

            <PageHeader
                eyebrow={t('gallery.eyebrow')}
                title={t('gallery.title')}
                description={t('gallery.description')}
            />

            <section className="bg-cream py-12 md:py-16">
                <div className="mx-auto max-w-6xl px-4 sm:px-6">
                    {categories.length > 0 && (
                        <div
                            role="group"
                            aria-label={t('gallery.filter')}
                            className="mb-8 flex flex-wrap gap-2"
                        >
                            {[null, ...categories].map((name) => (
                                <button
                                    key={name ?? 'all'}
                                    type="button"
                                    aria-pressed={category === name}
                                    onClick={() => setCategory(name)}
                                    className={cn(
                                        'rounded-full border px-4 py-1.5 text-sm font-medium transition focus-visible:ring-2 focus-visible:ring-forest-600 focus-visible:ring-offset-2 focus-visible:outline-none',
                                        category === name
                                            ? 'border-forest-800 bg-forest-800 text-cream'
                                            : 'border-forest-200 bg-white text-forest-900 hover:border-forest-600',
                                    )}
                                >
                                    {name ?? t('gallery.all')}
                                </button>
                            ))}
                        </div>
                    )}

                    {visible.length === 0 ? (
                        <p className="py-16 text-center text-forest-800/80">
                            {t('gallery.empty')}
                        </p>
                    ) : (
                        <ul className="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4">
                            {visible.map((item, index) => (
                                <li key={item.id}>
                                    <button
                                        type="button"
                                        onClick={() => setActive(index)}
                                        aria-label={t('gallery.open', {
                                            title:
                                                item.title ??
                                                t('gallery.untitled'),
                                        })}
                                        className="group relative block aspect-square w-full overflow-hidden rounded-xl bg-forest-100 focus-visible:ring-2 focus-visible:ring-forest-600 focus-visible:ring-offset-2 focus-visible:outline-none md:aspect-[4/3]"
                                    >
                                        <img
                                            src={item.image}
                                            alt=""
                                            loading="lazy"
                                            className="size-full object-cover transition duration-500 group-hover:scale-105"
                                        />
                                        {item.youtube_id && (
                                            <span className="absolute inset-0 flex items-center justify-center bg-forest-950/30">
                                                <span className="flex size-12 items-center justify-center rounded-full bg-cream/90 text-forest-900">
                                                    <Play
                                                        className="size-5 fill-current"
                                                        aria-hidden="true"
                                                    />
                                                </span>
                                            </span>
                                        )}
                                        {item.title && (
                                            <span className="absolute inset-x-0 bottom-0 bg-linear-to-t from-forest-950/80 to-transparent p-3 text-left text-xs font-medium text-cream opacity-0 transition group-hover:opacity-100 group-focus-visible:opacity-100">
                                                {item.title}
                                            </span>
                                        )}
                                    </button>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </section>

            <dialog
                ref={dialogRef}
                aria-label={current?.title ?? t('gallery.untitled')}
                onClose={() => setActive(null)}
                onClick={(event) => {
                    if (event.target === event.currentTarget) {
                        setActive(null);
                    }
                }}
                onKeyDown={(event) => {
                    if (event.key === 'ArrowLeft') {
                        move(-1);
                    }

                    if (event.key === 'ArrowRight') {
                        move(1);
                    }
                }}
                className="m-0 size-full max-h-none max-w-none bg-transparent p-0 text-cream backdrop:bg-forest-950/90 backdrop:backdrop-blur-sm"
            >
                {current && (
                    <div className="pointer-events-none flex size-full flex-col items-center justify-center gap-4 p-4 sm:p-10">
                        {current.youtube_id ? (
                            <div className="pointer-events-auto aspect-video w-full max-w-4xl overflow-hidden rounded-lg bg-black">
                                <iframe
                                    key={current.id}
                                    src={`https://www.youtube-nocookie.com/embed/${current.youtube_id}?autoplay=1&rel=0`}
                                    title={current.title ?? t('gallery.video')}
                                    allow="autoplay; encrypted-media; picture-in-picture; fullscreen"
                                    allowFullScreen
                                    className="size-full"
                                />
                            </div>
                        ) : (
                            <img
                                src={current.image}
                                alt={current.title ?? t('gallery.untitled')}
                                className="pointer-events-auto max-h-[78vh] w-auto max-w-full rounded-lg object-contain"
                            />
                        )}
                        {(current.title || current.category) && (
                            <p className="pointer-events-auto max-w-2xl text-center text-sm text-forest-100">
                                {current.title}
                                {current.title && current.category && ' · '}
                                {current.category}
                            </p>
                        )}
                    </div>
                )}

                <button
                    type="button"
                    onClick={() => setActive(null)}
                    aria-label={t('gallery.close')}
                    className={cn(lightboxButton, 'top-4 right-4')}
                >
                    <X className="size-6" aria-hidden="true" />
                </button>
                {visible.length > 1 && (
                    <>
                        <button
                            type="button"
                            onClick={() => move(-1)}
                            aria-label={t('gallery.prev')}
                            className={cn(
                                lightboxButton,
                                'top-1/2 left-3 -translate-y-1/2',
                            )}
                        >
                            <ChevronLeft
                                className="size-6"
                                aria-hidden="true"
                            />
                        </button>
                        <button
                            type="button"
                            onClick={() => move(1)}
                            aria-label={t('gallery.next')}
                            className={cn(
                                lightboxButton,
                                'top-1/2 right-3 -translate-y-1/2',
                            )}
                        >
                            <ChevronRight
                                className="size-6"
                                aria-hidden="true"
                            />
                        </button>
                    </>
                )}
            </dialog>
        </>
    );
}
