import { Head, Link } from '@inertiajs/react';
import SimplePagination from '@/components/simple-pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDateTime, formatRupiah } from '@/lib/format';
import { eventStatusLabels } from '@/lib/status';
import { create, edit } from '@/routes/admin/events';

type Row = {
    id: number;
    title: string;
    status: string;
    starts_at: string;
    fee: number;
    quota: number | null;
    taken: number;
};

type Props = {
    events: {
        data: Row[];
        prev_page_url: string | null;
        next_page_url: string | null;
        current_page: number;
        last_page: number;
        total: number;
    };
};

export default function AdminEventsIndex({ events }: Props) {
    return (
        <>
            <Head title="Acara" />
            <div className="space-y-4 p-4">
                <div className="flex items-center">
                    <h1 className="mr-auto text-2xl font-semibold">Acara</h1>
                    <Button asChild>
                        <Link href={create()}>Tambah acara</Link>
                    </Button>
                </div>

                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="p-3">Acara</th>
                                <th className="p-3">Waktu</th>
                                <th className="p-3">Biaya</th>
                                <th className="p-3">Peserta</th>
                                <th className="p-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {events.data.map((row) => (
                                <tr
                                    key={row.id}
                                    className="border-b last:border-0"
                                >
                                    <td className="p-3">
                                        <Link
                                            href={edit(row.id)}
                                            className="font-medium underline"
                                        >
                                            {row.title}
                                        </Link>
                                    </td>
                                    <td className="p-3">
                                        {formatDateTime(row.starts_at)}
                                    </td>
                                    <td className="p-3">
                                        {row.fee > 0
                                            ? formatRupiah(row.fee)
                                            : 'Gratis'}
                                    </td>
                                    <td className="p-3">
                                        {row.taken}
                                        {row.quota !== null &&
                                            ` / ${row.quota}`}
                                    </td>
                                    <td className="p-3">
                                        <Badge variant="secondary">
                                            {eventStatusLabels[row.status] ??
                                                row.status}
                                        </Badge>
                                    </td>
                                </tr>
                            ))}
                            {events.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={5}
                                        className="p-6 text-center text-muted-foreground"
                                    >
                                        Belum ada acara.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <SimplePagination page={events} />
            </div>
        </>
    );
}
