import { Link } from '@inertiajs/react';
import { useTranslation } from '@/lib/i18n';
import facilities from '@/routes/facilities';

export default function FinalCta({ whatsappUrl }: { whatsappUrl?: string }) {
    const { t } = useTranslation();

    return (
        <section className="bg-forest-800 py-16 text-cream">
            <div className="mx-auto max-w-3xl px-4 text-center">
                <h2 className="font-serif text-3xl font-semibold md:text-4xl">
                    {t('cta.title')}
                </h2>
                <p className="mt-4 text-forest-100">{t('cta.description')}</p>
                <div className="mt-8 flex flex-wrap justify-center gap-3">
                    <Link
                        href={facilities.index()}
                        className="rounded-md bg-clay px-7 py-3 text-xs font-semibold tracking-wider text-white uppercase transition hover:bg-clay-dark"
                    >
                        {t('facilities.cta')}
                    </Link>
                    {whatsappUrl && (
                        <a
                            href={whatsappUrl}
                            target="_blank"
                            rel="noreferrer"
                            className="rounded-md border border-cream/50 px-7 py-3 text-xs font-semibold tracking-wider uppercase transition hover:bg-cream/10"
                        >
                            {t('cta.contact')}
                        </a>
                    )}
                </div>
            </div>
        </section>
    );
}
