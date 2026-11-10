import Media from '@/components/landing/media';
import SectionHeading from '@/components/landing/section-heading';
import { formatDate } from '@/lib/format';
import { useTranslation, type MessageKey } from '@/lib/i18n';
import type { ArticleSummary } from '@/types/landing';

const categoryKeys: Record<string, MessageKey> = {
    culture: 'stories.culture',
    village_potential: 'stories.village_potential',
    community: 'stories.community',
};

export default function Stories({ articles }: { articles: ArticleSummary[] }) {
    const { t, locale } = useTranslation();

    if (articles.length === 0) {
        return null;
    }

    return (
        <section id="cerita" className="scroll-mt-24 bg-cream py-20">
            <div className="mx-auto max-w-6xl px-4">
                <SectionHeading
                    centered
                    eyebrow={t('stories.eyebrow')}
                    title={t('stories.title')}
                    description={t('stories.description')}
                />

                <div className="mt-12 grid gap-6 md:grid-cols-3">
                    {articles.map((article) => (
                        <article
                            key={article.id}
                            className="flex flex-col overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm"
                        >
                            <div className="aspect-[16/10]">
                                <Media
                                    src={article.image}
                                    alt={article.title}
                                />
                            </div>
                            <div className="flex flex-1 flex-col p-5">
                                <p className="text-[11px] font-semibold tracking-[0.2em] text-clay uppercase">
                                    {categoryKeys[article.category]
                                        ? t(categoryKeys[article.category])
                                        : article.category}
                                </p>
                                <h3 className="mt-2 font-serif text-lg leading-snug font-semibold text-forest-900">
                                    {article.title}
                                </h3>
                                {article.excerpt && (
                                    <p className="mt-2 line-clamp-3 text-sm leading-relaxed text-forest-800/80">
                                        {article.excerpt}
                                    </p>
                                )}
                                <p className="mt-auto pt-4 text-xs text-forest-700">
                                    {article.published_at &&
                                        formatDate(
                                            article.published_at.slice(0, 10),
                                            locale,
                                        )}
                                    {article.author && ` · ${article.author}`}
                                </p>
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}
