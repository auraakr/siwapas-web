import { Link } from '@inertiajs/react';
import { ChevronDown, Play } from 'lucide-react';
import { useTranslation } from '@/lib/i18n';
import facilities from '@/routes/facilities';

export default function Hero({
    image,
    videoUrl,
}: {
    image: string | null;
    videoUrl: string | null;
}) {
    const { t } = useTranslation();

    return (
        <section className="relative isolate overflow-hidden bg-forest-900 text-cream">
            {image ? (
                <img
                    src={image}
                    alt=""
                    fetchPriority="high"
                    className="absolute inset-0 -z-20 size-full object-cover"
                />
            ) : (
                <div className="absolute inset-0 -z-20 bg-linear-to-br from-forest-950 via-forest-800 to-forest-600" />
            )}
            <div className="absolute inset-0 -z-10 bg-linear-to-t from-forest-950/85 via-forest-950/40 to-forest-950/30" />

            <div className="mx-auto flex min-h-[560px] max-w-6xl flex-col justify-center px-4 py-24 sm:px-6 md:min-h-[640px]">
                <p className="mb-5 inline-flex w-fit items-center gap-2 rounded-full border border-cream/30 bg-forest-950/40 px-4 py-1.5 text-[11px] font-semibold tracking-[0.2em] uppercase backdrop-blur">
                    <span className="size-1.5 rounded-full bg-brand-yellow" />
                    {t('hero.badge')}
                </p>

                <h1 className="font-serif text-5xl leading-[1.05] font-semibold md:text-7xl">
                    Pasirnanjung
                    <span className="block text-brand-yellow italic">
                        Geulis
                    </span>
                </h1>

                <p className="mt-6 max-w-xl text-base leading-relaxed text-forest-100 md:text-lg">
                    {t('hero.description')}
                </p>

                <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <a
                        href="#destinasi"
                        className="rounded-md bg-clay-dark px-6 py-3 text-center text-xs font-semibold tracking-wider text-white uppercase transition hover:bg-clay focus-visible:ring-2 focus-visible:ring-cream focus-visible:ring-offset-2 focus-visible:ring-offset-forest-900 focus-visible:outline-none"
                    >
                        {t('hero.explore')}
                    </a>
                    <Link
                        href={facilities.index()}
                        className="rounded-md border border-cream/60 px-6 py-3 text-center text-xs font-semibold tracking-wider uppercase backdrop-blur transition hover:bg-cream/10 focus-visible:ring-2 focus-visible:ring-cream focus-visible:outline-none"
                    >
                        {t('hero.rent')}
                    </Link>
                    {videoUrl && (
                        <a
                            href={videoUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="flex items-center justify-center gap-2 rounded-md px-4 py-3 text-xs font-semibold tracking-wider uppercase transition hover:text-brand-yellow focus-visible:ring-2 focus-visible:ring-cream focus-visible:outline-none"
                        >
                            <Play className="size-4" aria-hidden="true" />
                            {t('hero.video')}
                        </a>
                    )}
                </div>
            </div>

            <a
                href="#tentang"
                aria-label={t('hero.scroll')}
                className="absolute bottom-5 left-1/2 -translate-x-1/2 text-cream/70 motion-safe:animate-bounce"
            >
                <ChevronDown className="size-6" />
            </a>
        </section>
    );
}