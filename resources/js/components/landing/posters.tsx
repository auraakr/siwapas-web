import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useRef } from 'react';
import SectionHeading from '@/components/landing/section-heading';
import { useTranslation } from '@/lib/i18n';
import type { PosterItem } from '@/types/landing';

export default function Posters({ items }: { items: PosterItem[] }) {
    const { t } = useTranslation();
    const track = useRef<HTMLUListElement>(null);

    if (items.length === 0) {
        return null;
    }

    const scrollBy = (direction: 1 | -1) => {
        const element = track.current;

        if (element) {
            element.scrollBy({
                left: direction * element.clientWidth * 0.8,
                behavior: 'smooth',
            });
        }
    };

    return (
        <section id="poster" className="scroll-mt-24 bg-cream py-20">
            <div className="mx-auto max-w-5xl px-4">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <SectionHeading
                        eyebrow={t('posters.eyebrow')}
                        title={t('posters.title')}
                    />
                    {items.length > 1 && (
                        <div className="flex gap-2">
                            <button
                                type="button"
                                onClick={() => scrollBy(-1)}
                                aria-label={t('posters.prev')}
                                className="rounded-full border border-forest-100 bg-white p-2.5 text-forest-800 transition hover:bg-forest-800 hover:text-cream"
                            >
                                <ChevronLeft className="size-5" />
                            </button>
                            <button
                                type="button"
                                onClick={() => scrollBy(1)}
                                aria-label={t('posters.next')}
                                className="rounded-full border border-forest-100 bg-white p-2.5 text-forest-800 transition hover:bg-forest-800 hover:text-cream"
                            >
                                <ChevronRight className="size-5" />
                            </button>
                        </div>
                    )}
                </div>

                <ul
                    ref={track}
                    className="mt-10 flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                >
                    {items.map((poster) => (
                        <li
                            key={poster.id}
                            className="w-64 shrink-0 snap-start sm:w-72"
                        >
                            <figure className="overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm">
                                <img
                                    src={poster.image}
                                    alt={poster.alt}
                                    loading="lazy"
                                    className="aspect-[3/4] w-full object-cover"
                                />
                                <figcaption className="px-4 py-3 font-serif text-sm font-semibold text-forest-900">
                                    {poster.title}
                                </figcaption>
                            </figure>
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}
