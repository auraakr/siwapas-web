import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import FloatingWhatsapp from '@/components/site/floating-whatsapp';
import SiteFooter from '@/components/site/site-footer';
import SiteHeader from '@/components/site/site-header';
import { useTranslation } from '@/lib/i18n';

/**
 * Header and footer shared by every public page; the page decides its own width.
 */
export default function SiteLayout({ children }: { children: ReactNode }) {
    const { site } = usePage().props;
    const { locale } = useTranslation();

    return (
        <div className="flex min-h-screen flex-col bg-cream text-forest-950">
            <a
                href="#konten"
                className="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:rounded-md focus:bg-forest-900 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-cream"
            >
                {locale === 'en' ? 'Skip to content' : 'Lewati ke konten'}
            </a>
            <SiteHeader />
            <main id="konten" className="flex-1">
                {children}
            </main>
            <SiteFooter />
            <FloatingWhatsapp whatsappUrl={site.contact.whatsapp_url} />
        </div>
    );
}