import type { ReactNode } from 'react';
import SiteLayout from '@/layouts/site-layout';

/**
 * Public inner pages: the shared site header and footer around a centred content column.
 */
export default function PublicLayout({ children }: { children: ReactNode }) {
    return (
        <SiteLayout>
            <div className="mx-auto max-w-4xl px-4 py-10">{children}</div>
        </SiteLayout>
    );
}
