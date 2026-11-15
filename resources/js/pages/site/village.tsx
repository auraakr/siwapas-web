import { Head } from '@inertiajs/react';
import Media from '@/components/landing/media';
import SectionHeading from '@/components/landing/section-heading';
import PageHeader from '@/components/site/page-header';
import { useTranslation } from '@/lib/i18n';
import type { PageContent, TeamMember } from '@/types/site';

export default function Village({
    story,
    vision,
    team,
}: {
    story: PageContent | null;
    vision: PageContent | null;
    team: TeamMember[];
}) {
    const { t } = useTranslation();
    const description = story?.seo_description ?? t('village.description');

    return (
        <>
            <Head title={story?.seo_title ?? t('village.eyebrow')}>
                <meta name="description" content={description} />
            </Head>

            <PageHeader
                eyebrow={t('village.eyebrow')}
                title={t('village.title')}
                description={t('village.description')}
            />

            <section className="bg-cream py-14 md:py-20">
                <div className="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-[1fr_1.6fr]">
                    <SectionHeading
                        eyebrow={t('village.story')}
                        title={story?.title ?? t('about.title')}
                    />
                    {story?.body ? (
                        <div
                            className="rich-text"
                            dangerouslySetInnerHTML={{ __html: story.body }}
                        />
                    ) : (
                        <div className="space-y-4 leading-relaxed text-forest-800/80">
                            <p>{t('about.p1')}</p>
                            <p>{t('about.p2')}</p>
                        </div>
                    )}
                </div>
            </section>

            {vision?.body && (
                <section className="bg-cream-dark py-14 md:py-20">
                    <div className="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-[1fr_1.6fr]">
                        <SectionHeading
                            eyebrow={t('village.vision')}
                            title={vision.title}
                        />
                        <div
                            className="rich-text"
                            dangerouslySetInnerHTML={{ __html: vision.body }}
                        />
                    </div>
                </section>
            )}

            {team.length > 0 && (
                <section className="bg-cream py-14 md:py-20">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6">
                        <SectionHeading
                            eyebrow={t('village.team')}
                            title={t('village.team')}
                            description={t('village.teamDescription')}
                        />
                        <ul className="mt-10 grid grid-cols-2 gap-5 md:grid-cols-3 lg:grid-cols-4">
                            {team.map((member) => (
                                <li key={member.id}>
                                    <div className="aspect-square overflow-hidden rounded-2xl bg-forest-100">
                                        <Media
                                            src={member.image}
                                            alt={member.name}
                                        />
                                    </div>
                                    <h3 className="mt-3 font-serif text-lg font-semibold text-forest-900">
                                        {member.name}
                                    </h3>
                                    <p className="text-sm font-medium text-clay-dark">
                                        {member.role}
                                    </p>
                                    {member.bio && (
                                        <p className="mt-1 text-sm text-forest-800/80">
                                            {member.bio}
                                        </p>
                                    )}
                                </li>
                            ))}
                        </ul>
                    </div>
                </section>
            )}
        </>
    );
}
