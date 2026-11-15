import type { ReactNode } from 'react';
import SiteLayout from '@/layouts/site-layout';

/**
 * Public inner pages (booking, events, orders): the shared site header and footer around a content
 * column. The `site-theme` class re-colours the shadcn form components to the village palette, and
 * each page decides how narrow its own content should be.
 */
export default function PublicLayout({ children }: { children: ReactNode }) {
    return (
        <SiteLayout>
            <div className="site-theme mx-auto max-w-6xl px-4 py-10 sm:px-6 md:py-14">
                {children}
            </div>
        </SiteLayout>
    );
}
