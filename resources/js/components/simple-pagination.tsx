import { Link } from '@inertiajs/react';

type Paginator = {
    prev_page_url: string | null;
    next_page_url: string | null;
    current_page: number;
    last_page: number;
    total: number;
};

export default function SimplePagination({ page }: { page: Paginator }) {
    return (
        <div className="flex items-center justify-between text-sm">
            <span className="text-muted-foreground">
                {page.total} data · halaman {page.current_page} dari{' '}
                {page.last_page}
            </span>
            <div className="flex gap-3">
                {page.prev_page_url && (
                    <Link href={page.prev_page_url} preserveScroll>
                        Sebelumnya
                    </Link>
                )}
                {page.next_page_url && (
                    <Link href={page.next_page_url} preserveScroll>
                        Berikutnya
                    </Link>
                )}
            </div>
        </div>
    );
}
