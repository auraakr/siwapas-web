import { Music, Tent, TreePine, Users, type LucideIcon } from 'lucide-react';
import SectionHeading from '@/components/landing/section-heading';
import { useTranslation, type MessageKey } from '@/lib/i18n';

const pillars: { icon: LucideIcon; title: MessageKey; text: MessageKey }[] = [
    {
        icon: TreePine,
        title: 'experience.nature.title',
        text: 'experience.nature.text',
    },
    {
        icon: Tent,
        title: 'experience.adventure.title',
        text: 'experience.adventure.text',
    },
    {
        icon: Music,
        title: 'experience.culture.title',
        text: 'experience.culture.text',
    },
    {
        icon: Users,
        title: 'experience.together.title',
        text: 'experience.together.text',
    },
];

/**
 * What a visit offers, in the client's own words: nature, adventure, culture and time together.
 */
export default function Experience() {
    const { t } = useTranslation();

    return (
        <section id="pengalaman" className="scroll-mt-24 bg-cream py-20">
            <div className="mx-auto max-w-6xl px-4">
                <SectionHeading
                    centered
                    eyebrow={t('experience.eyebrow')}
                    title={t('experience.title')}
                    description={t('experience.description')}
                />

                <ul className="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    {pillars.map(({ icon: Icon, title, text }) => (
                        <li
                            key={title}
                            className="rounded-2xl border border-forest-100 bg-white p-6 shadow-sm"
                        >
                            <span className="flex size-12 items-center justify-center rounded-xl bg-forest-800 text-cream">
                                <Icon className="size-6" aria-hidden="true" />
                            </span>
                            <h3 className="mt-5 font-serif text-lg font-semibold text-forest-900">
                                {t(title)}
                            </h3>
                            <p className="mt-2 text-sm leading-relaxed text-forest-800/80">
                                {t(text)}
                            </p>
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}
