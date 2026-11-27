import { Link } from '@inertiajs/react';
import { ArrowRight, MapPin } from 'lucide-react';
import Media from '@/components/landing/media';
import SectionHeading from '@/components/landing/section-heading';
import { useTranslation } from '@/lib/i18n';

export default function About({
    image,
    address,
}: {
    image: string | null;
    address?: string;
}) {
    const { t } = useTranslation();
    const highlights = [
        t('about.point1'),
        t('about.point2'),
        t('about.point3'),
    ];

    return (
        <section id="tentang" className="scroll-mt-24 bg-cream py-20">
            <div className="mx-auto grid max-w-6xl items-center gap-12 px-4 lg:grid-cols-2">
                <div>
                    <SectionHeading
                        eyebrow={t('about.eyebrow')}
                        title={t('about.title')}
                    />
                    <p className="mt-6 leading-relaxed text-forest-800/80">
                        {t('about.p1')}
                    </p>
                    <ul className="mt-6 space-y-2 text-sm font-medium text-forest-900">
                        {highlights.map((item) => (
                            <li key={item} className="flex items-center gap-2">
                                <span className="size-2 rounded-full bg-clay" />
                                {item}
                            </li>
                        ))}
                    </ul>
                    <Link
                        href="/profil-desa"
                        className="mt-8 inline-flex items-center gap-2 rounded-md border border-forest-800 px-5 py-2.5 text-sm font-semibold text-forest-900 transition hover:bg-forest-800 hover:text-cream"
                    >
                        {t('about.more')}
                        <ArrowRight className="size-4" aria-hidden="true" />
                    </Link>
                </div>

                <div className="relative mx-auto w-full max-w-md">
                    <div className="aspect-[3/4] overflow-hidden rounded-t-[999px] rounded-b-3xl border-8 border-white shadow-xl">
                        <Media src={image} alt={t('about.imageAlt')} />
                    </div>
                    {address && (
                        <p className="absolute right-4 -bottom-5 left-4 flex items-center gap-2 rounded-xl bg-white px-4 py-3 text-xs font-medium text-forest-900 shadow-lg">
                            <MapPin className="size-4 shrink-0 text-clay" />
                            {address}
                        </p>
                    )}
                </div>
            </div>
        </section>
    );
}
