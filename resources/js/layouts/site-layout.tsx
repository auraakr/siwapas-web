import type { ReactNode } from 'react';
import SiteFooter from '@/components/site/site-footer';
import SiteHeader from '@/components/site/site-header';

/**
 * Header and footer shared by every public page; the page decides its own width.
 */
export default function SiteLayout({ children }: { children: ReactNode }) {
    return (
        <div className="flex min-h-screen flex-col bg-cream text-forest-950">
            <SiteHeader />
            <main className="flex-1">{children}</main>
            <SiteFooter />
        </div>
    );
}
