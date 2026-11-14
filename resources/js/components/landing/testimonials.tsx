import { useTranslation } from '@/lib/i18n';

type Testimonial = { name: string; origin?: string; quote: string };

// Isi dengan ulasan asli, contoh:
// { name: 'Nama Pengunjung', origin: 'Bandung', quote: 'Isi ulasan...' },
const testimonials: Testimonial[] = [];

type Props = { reviewsUrl?: string | null };

export default function Testimonials({ reviewsUrl }: Props) {
    const { locale } = useTranslation();

    if (testimonials.length === 0) {
        return null;
    }

    return (
        <section className="bg-cream-dark py-16 sm:py-20">
            <div className="mx-auto max-w-6xl px-4 sm:px-6">
                <div className="mb-10 text-center">
                    <p className="text-xs font-semibold tracking-[0.2em] text-clay-dark uppercase">
                        {locale === 'en' ? 'Visitor voices' : 'Kata pengunjung'}
                    </p>
                    <h2 className="mt-2 font-serif text-3xl text-forest-900 sm:text-4xl">
                        {locale === 'en'
                            ? 'Stories from our guests'
                            : 'Cerita dari para tamu'}
                    </h2>
                </div>

                <ul className="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 md:mx-0 md:grid md:grid-cols-3 md:overflow-visible md:px-0">
                    {testimonials.map((item) => (
                        <li
                            key={item.name}
                            className="w-[85%] shrink-0 snap-start rounded-2xl bg-white p-6 shadow-sm md:w-auto"
                        >
                            <blockquote className="text-forest-900">
                                “{item.quote}”
                            </blockquote>
                            <p className="mt-4 text-sm font-semibold text-forest-800">
                                {item.name}
                                {item.origin && (
                                    <span className="font-normal text-forest-700">
                                        {' '}
                                        · {item.origin}
                                    </span>
                                )}
                            </p>
                        </li>
                    ))}
                </ul>

                {reviewsUrl && (
                    <p className="mt-6 text-center">
                        <a
                            href={reviewsUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-sm font-semibold text-clay-dark underline underline-offset-4"
                        >
                            {locale === 'en'
                                ? 'Read more reviews on Google Maps'
                                : 'Baca ulasan lainnya di Google Maps'}
                        </a>
                    </p>
                )}
            </div>
        </section>
    );
}