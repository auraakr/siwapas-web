import { useTranslation } from '@/lib/i18n';

type Partner = { name: string; logo?: string; href?: string };

// Tambah mitra di sini. Kalau ada file logo, taruh di public/images/partners/
// lalu isi `logo: '/images/partners/nama-file.png'`.
const partners: Partner[] = [
    { name: 'Pokdarwis Pasirnanjung' },
    { name: 'BUMDes Pasirnanjung Mandiri' },
    { name: 'Pemerintah Desa Pasirnanjung' },
];

export default function Partners() {
    const { locale } = useTranslation();

    if (partners.length === 0) {
        return null;
    }

    return (
        <section
            aria-label={locale === 'en' ? 'Our partners' : 'Mitra kami'}
            className="border-y border-forest-200 bg-cream py-10"
        >
            <div className="mx-auto max-w-6xl px-4 sm:px-6">
                <p className="mb-6 text-center text-xs font-semibold tracking-[0.2em] text-forest-700 uppercase">
                    {locale === 'en'
                        ? 'Managed together with'
                        : 'Dikelola bersama'}
                </p>
                <ul className="flex flex-wrap items-center justify-center gap-x-10 gap-y-6">
                    {partners.map((partner) => {
                        const content = partner.logo ? (
                            <img
                                src={partner.logo}
                                alt={partner.name}
                                loading="lazy"
                                className="h-12 w-auto object-contain opacity-80 grayscale transition hover:opacity-100 hover:grayscale-0"
                            />
                        ) : (
                            <span className="font-serif text-lg text-forest-900">
                                {partner.name}
                            </span>
                        );

                        return (
                            <li key={partner.name}>
                                {partner.href ? (
                                    <a
                                        href={partner.href}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        {content}
                                    </a>
                                ) : (
                                    content
                                )}
                            </li>
                        );
                    })}
                </ul>
            </div>
        </section>
    );
}