import { Head, usePage } from '@inertiajs/react';
import { Clock, ExternalLink, MapPin } from 'lucide-react';
import type { ReactNode } from 'react';
import Media from '@/components/landing/media';
import PageHeader from '@/components/site/page-header';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import type { TeamMember, VillageSection } from '@/types/site';

function TeamGrid({ team }: { team: TeamMember[] }) {
    return (
        <ul className="mt-10 grid grid-cols-2 gap-5 md:grid-cols-3 lg:grid-cols-4">
            {team.map((member) => (
                <li key={member.id}>
                    <div className="aspect-square overflow-hidden rounded-2xl bg-forest-100">
                        <Media src={member.image} alt={member.name} />
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
    );
}

/**
 * One block of the profile: the text, with its photo beside it (swapping sides block by block).
 */
function Block({
    section,
    index,
    children,
}: {
    section: VillageSection;
    index: number;
    children?: ReactNode;
}) {
    const hasImage = Boolean(section.image);

    return (
        <section
            id={section.anchor}
            className={cn(
                'scroll-mt-28 py-14 md:py-20',
                index % 2 === 0 ? 'bg-cream' : 'bg-cream-dark',
            )}
        >
            <div className="mx-auto max-w-6xl px-4 sm:px-6">
                <div
                    className={cn(
                        'grid gap-10',
                        hasImage && 'lg:grid-cols-2 lg:items-center',
                    )}
                >
                    <div
                        className={cn(
                            hasImage && index % 2 === 1 && 'lg:order-2',
                        )}
                    >
                        <h2 className="font-serif text-3xl leading-tight font-semibold text-forest-900 md:text-4xl">
                            {section.title}
                        </h2>
                        {section.body && (
                            <div
                                className="rich-text mt-5"
                                dangerouslySetInnerHTML={{
                                    __html: section.body,
                                }}
                            />
                        )}
                    </div>
                    {hasImage && (
                        <div className="aspect-[4/3] overflow-hidden rounded-2xl bg-forest-100 shadow-sm">
                            <Media src={section.image} alt={section.title} />
                        </div>
                    )}
                </div>
                {children}
            </div>
        </section>
    );
}

export default function Village({
    sections,
    team,
}: {
    sections: VillageSection[];
    team: TeamMember[];
}) {
    const { site } = usePage().props;
    const { t } = useTranslation();
    const contact = site.contact;
    const intro = sections.find((section) => section.key === 'profile');
    const hasManagement = sections.some(
        (section) => section.key === 'management',
    );
    const hasLocation = Boolean(contact.address || contact.maps_url);
    const description = intro?.seo_description ?? t('village.description');

    const chips = [
        ...sections.map((section) => ({
            anchor: section.anchor,
            label: section.title,
        })),
        ...(!hasManagement && team.length > 0
            ? [{ anchor: 'pengelola', label: t('village.team') }]
            : []),
        ...(hasLocation
            ? [{ anchor: 'lokasi', label: t('village.location') }]
            : []),
    ];

    return (
        <>
            <Head title={intro?.seo_title ?? t('village.eyebrow')}>
                <meta name="description" content={description} />
            </Head>

            <PageHeader
                eyebrow={t('village.eyebrow')}
                title={t('village.title')}
                description={t('village.description')}
            >
                {chips.length > 1 && (
                    <nav
                        aria-label={t('village.sections')}
                        className="mt-8 flex flex-wrap gap-2"
                    >
                        {chips.map((chip) => (
                            <a
                                key={chip.anchor}
                                href={`#${chip.anchor}`}
                                className="rounded-full border border-cream/30 px-4 py-1.5 text-sm font-medium text-cream transition hover:bg-cream/10 focus-visible:ring-2 focus-visible:ring-brand-yellow focus-visible:outline-none"
                            >
                                {chip.label}
                            </a>
                        ))}
                    </nav>
                )}
            </PageHeader>

            {sections.length === 0 && (
                <section className="bg-cream py-14 md:py-20">
                    <div className="mx-auto max-w-3xl space-y-4 px-4 leading-relaxed text-forest-800/80 sm:px-6">
                        <p>{t('about.p1')}</p>
                        <p>{t('about.p2')}</p>
                    </div>
                </section>
            )}

            {sections.map((section, index) => (
                <Block key={section.key} section={section} index={index}>
                    {section.key === 'management' && team.length > 0 && (
                        <TeamGrid team={team} />
                    )}
                </Block>
            ))}

            {!hasManagement && team.length > 0 && (
                <section
                    id="pengelola"
                    className={cn(
                        'scroll-mt-28 py-14 md:py-20',
                        sections.length % 2 === 0
                            ? 'bg-cream'
                            : 'bg-cream-dark',
                    )}
                >
                    <div className="mx-auto max-w-6xl px-4 sm:px-6">
                        <h2 className="font-serif text-3xl font-semibold text-forest-900 md:text-4xl">
                            {t('village.team')}
                        </h2>
                        <p className="mt-3 max-w-2xl text-forest-800/80">
                            {t('village.teamDescription')}
                        </p>
                        <TeamGrid team={team} />
                    </div>
                </section>
            )}

            {hasLocation && (
                <section
                    id="lokasi"
                    className="scroll-mt-28 bg-forest-900 py-14 text-cream md:py-20"
                >
                    <div className="mx-auto max-w-6xl px-4 sm:px-6">
                        <h2 className="font-serif text-3xl font-semibold md:text-4xl">
                            {t('village.location')}
                        </h2>
                        <div className="mt-6 space-y-3 text-forest-100">
                            {contact.address && (
                                <p className="flex gap-3">
                                    <MapPin
                                        className="mt-1 size-4 shrink-0 text-brand-yellow"
                                        aria-hidden="true"
                                    />
                                    {contact.address}
                                </p>
                            )}
                            {contact.hours && (
                                <p className="flex gap-3">
                                    <Clock
                                        className="mt-1 size-4 shrink-0 text-brand-yellow"
                                        aria-hidden="true"
                                    />
                                    {contact.hours}
                                </p>
                            )}
                        </div>
                        {contact.maps_url && (
                            <a
                                href={contact.maps_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="mt-8 inline-flex items-center gap-2 rounded-md bg-clay-dark px-6 py-3 text-xs font-semibold tracking-wider text-white uppercase transition hover:bg-clay focus-visible:ring-2 focus-visible:ring-cream focus-visible:ring-offset-2 focus-visible:ring-offset-forest-900 focus-visible:outline-none"
                            >
                                <ExternalLink
                                    className="size-4"
                                    aria-hidden="true"
                                />
                                {t('visit.map')}
                            </a>
                        )}
                    </div>
                </section>
            )}
        </>
    );
}
