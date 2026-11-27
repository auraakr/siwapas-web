import { Head } from '@inertiajs/react';
import { Navigation, MapPin, Quote } from 'lucide-react';
import { useEffect, useState, type ReactNode } from 'react';
import Media from '@/components/landing/media';
import PageHeader from '@/components/site/page-header';
import { useTranslation, type MessageKey } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import type { TeamMember } from '@/types/site';

type ProfileSection = { body: string; image: string | null };

type Location = {
    address: string | null;
    embed_url: string | null;
    directions_url: string | null;
};

type Props = {
    about: ProfileSection | null;
    history: ProfileSection | null;
    vision: string | null;
    mission: string[];
    management: ProfileSection | null;
    team: TeamMember[];
    potential: ProfileSection | null;
    location: Location | null;
    seo: { title: string | null; description: string | null };
};

type Part = {
    anchor: string;
    label: MessageKey;
    heading: MessageKey;
    content: ReactNode;
    image?: string | null;
};

function RichText({ html }: { html: string }) {
    return (
        <div
            className="rich-text mt-5"
            dangerouslySetInnerHTML={{ __html: html }}
        />
    );
}

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

function VisionMission({
    vision,
    mission,
}: {
    vision: string | null;
    mission: string[];
}) {
    const { t } = useTranslation();

    return (
        <div className="mt-8 grid gap-10 lg:grid-cols-[1fr_1.2fr]">
            {vision && (
                <figure>
                    <p className="text-xs font-semibold tracking-[0.2em] text-clay-dark uppercase">
                        {t('village.visionLabel')}
                    </p>
                    <blockquote className="relative mt-4 rounded-2xl bg-forest-900 p-8 text-cream">
                        <Quote
                            className="absolute -top-4 left-6 size-9 rounded-full bg-clay-dark p-2 text-cream"
                            aria-hidden="true"
                        />
                        <p className="font-serif text-2xl leading-snug whitespace-pre-line md:text-3xl">
                            {vision}
                        </p>
                    </blockquote>
                </figure>
            )}
            {mission.length > 0 && (
                <div>
                    <p className="text-xs font-semibold tracking-[0.2em] text-clay-dark uppercase">
                        {t('village.missionLabel')}
                    </p>
                    <ol className="mt-4 space-y-3">
                        {mission.map((item, index) => (
                            <li
                                key={item}
                                className="flex gap-4 rounded-xl border border-forest-100 bg-white p-4 shadow-sm"
                            >
                                <span
                                    aria-hidden="true"
                                    className="flex size-8 shrink-0 items-center justify-center rounded-full bg-forest-100 font-semibold text-forest-800"
                                >
                                    {index + 1}
                                </span>
                                <span className="pt-1 leading-relaxed text-forest-900">
                                    {item}
                                </span>
                            </li>
                        ))}
                    </ol>
                </div>
            )}
        </div>
    );
}

function LocationBlock({ location }: { location: Location }) {
    const { t } = useTranslation();

    return (
        <div className="mt-8 grid gap-8 lg:grid-cols-[1fr_1.6fr] lg:items-start">
            <div className="space-y-5">
                {location.address && (
                    <p className="flex gap-3 text-lg text-forest-900">
                        <MapPin
                            className="mt-1 size-5 shrink-0 text-clay-dark"
                            aria-hidden="true"
                        />
                        {location.address}
                    </p>
                )}
                {location.directions_url && (
                    <a
                        href={location.directions_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-2 rounded-md bg-clay-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-clay focus-visible:ring-2 focus-visible:ring-clay focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        <Navigation className="size-4" aria-hidden="true" />
                        {t('village.directions')}
                    </a>
                )}
            </div>
            {location.embed_url && (
                <div className="aspect-[4/3] w-full overflow-hidden rounded-2xl border border-forest-100 bg-forest-100 shadow-sm sm:aspect-video">
                    <iframe
                        src={location.embed_url}
                        title={t('village.mapTitle')}
                        loading="lazy"
                        referrerPolicy="no-referrer-when-downgrade"
                        className="size-full border-0"
                        allowFullScreen
                    />
                </div>
            )}
        </div>
    );
}

/**
 * The section bar under the site header: stays visible while scrolling, scrolls sideways on
 * phones, and highlights the section being read.
 */
function SectionNav({ parts }: { parts: Part[] }) {
    const { t } = useTranslation();
    const [active, setActive] = useState(parts[0]?.anchor);

    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                const visible = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort(
                        (a, b) =>
                            a.boundingClientRect.top - b.boundingClientRect.top,
                    )[0];

                if (visible) {
                    setActive(visible.target.id);
                }
            },
            { rootMargin: '-30% 0px -60% 0px' },
        );

        parts.forEach((part) => {
            const element = document.getElementById(part.anchor);

            if (element) {
                observer.observe(element);
            }
        });

        return () => observer.disconnect();
    }, [parts]);

    // On phones the bar scrolls sideways: keep the highlighted section in view. Only the bar
    // scrolls; scrollIntoView would also move the page, since the bar is sticky.
    useEffect(() => {
        const link = document.querySelector<HTMLElement>(
            `[data-section-link="${active}"]`,
        );
        const bar = link?.closest('ul');

        if (!link || !bar) {
            return;
        }

        const linkLeft =
            link.getBoundingClientRect().left -
            bar.getBoundingClientRect().left +
            bar.scrollLeft;

        bar.scrollTo({
            left: linkLeft - (bar.clientWidth - link.offsetWidth) / 2,
            behavior: 'smooth',
        });
    }, [active]);

    return (
        <nav
            aria-label={t('village.sections')}
            className="sticky top-[var(--site-header-height,0px)] z-30 border-b border-forest-100 bg-cream/95 backdrop-blur"
        >
            <ul className="mx-auto flex max-w-6xl gap-1 overflow-x-auto px-4 sm:px-6">
                {parts.map((part) => (
                    <li key={part.anchor} className="shrink-0">
                        <a
                            href={`#${part.anchor}`}
                            data-section-link={part.anchor}
                            aria-current={
                                active === part.anchor ? 'location' : undefined
                            }
                            className={cn(
                                '-mb-px block border-b-2 px-3 py-3.5 text-sm font-semibold whitespace-nowrap transition',
                                active === part.anchor
                                    ? 'border-clay text-forest-900'
                                    : 'border-transparent text-forest-700/80 hover:text-forest-900',
                            )}
                        >
                            {t(part.label)}
                        </a>
                    </li>
                ))}
            </ul>
        </nav>
    );
}

export default function Village(props: Props) {
    const { t } = useTranslation();
    const { about, history, vision, mission, management, team, potential } =
        props;

    // Only sections with content appear, in the client's order; nothing is shown in their place.
    const candidates: (Part | null | false | '')[] = [
        about && {
            anchor: 'identitas',
            label: 'village.nav.about',
            heading: 'village.heading.about',
            content: <RichText html={about.body} />,
            image: about.image,
        },
        history && {
            anchor: 'sejarah',
            label: 'village.nav.history',
            heading: 'village.heading.history',
            content: <RichText html={history.body} />,
            image: history.image,
        },
        (vision || mission.length > 0) && {
            anchor: 'visi-misi',
            label: 'village.nav.vision',
            heading: 'village.heading.vision',
            content: <VisionMission vision={vision} mission={mission} />,
        },
        (management || team.length > 0) && {
            anchor: 'pengelola',
            label: 'village.nav.management',
            heading: 'village.heading.management',
            content: (
                <>
                    {management && <RichText html={management.body} />}
                    {team.length > 0 && <TeamGrid team={team} />}
                </>
            ),
            image: management?.image,
        },
        potential && {
            anchor: 'potensi',
            label: 'village.nav.potential',
            heading: 'village.heading.potential',
            content: <RichText html={potential.body} />,
            image: potential.image,
        },
        props.location && {
            anchor: 'lokasi',
            label: 'village.nav.location',
            heading: 'village.heading.location',
            content: <LocationBlock location={props.location} />,
        },
    ];
    const parts = candidates.filter((part): part is Part => Boolean(part));

    return (
        <>
            <Head title={props.seo.title ?? t('village.eyebrow')}>
                <meta
                    name="description"
                    content={props.seo.description ?? t('village.description')}
                />
            </Head>

            <PageHeader
                eyebrow={t('village.eyebrow')}
                title={t('village.title')}
                description={t('village.description')}
            />

            {parts.length > 1 && <SectionNav parts={parts} />}

            {parts.map((part, index) => {
                const hasImage = Boolean(part.image);

                return (
                    <section
                        key={part.anchor}
                        id={part.anchor}
                        style={{
                            scrollMarginTop:
                                'calc(var(--site-header-height, 0px) + 3.5rem)',
                        }}
                        className={cn(
                            'py-14 md:py-20',
                            index % 2 === 0 ? 'bg-cream' : 'bg-cream-dark',
                        )}
                    >
                        <div className="mx-auto max-w-6xl px-4 sm:px-6">
                            <div
                                className={cn(
                                    'grid gap-10',
                                    hasImage && 'lg:grid-cols-2 lg:items-start',
                                )}
                            >
                                <div
                                    className={cn(
                                        hasImage &&
                                            index % 2 === 1 &&
                                            'lg:order-2',
                                    )}
                                >
                                    <p className="text-xs font-semibold tracking-[0.2em] text-clay-dark uppercase">
                                        {t(part.label)}
                                    </p>
                                    <h2 className="mt-2 font-serif text-3xl leading-tight font-semibold text-forest-900 md:text-4xl">
                                        {t(part.heading)}
                                    </h2>
                                    {part.content}
                                </div>
                                {hasImage && part.image && (
                                    <div className="aspect-[4/3] overflow-hidden rounded-2xl bg-forest-100 shadow-sm">
                                        <Media
                                            src={part.image}
                                            alt={t(part.heading)}
                                        />
                                    </div>
                                )}
                            </div>
                        </div>
                    </section>
                );
            })}
        </>
    );
}
