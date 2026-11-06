import { Head, Link, router } from '@inertiajs/react';
import SimplePagination from '@/components/simple-pagination';
import { Badge } from '@/components/ui/badge';
import { formatDate, formatRupiah } from '@/lib/format';
import { orderStatusLabels } from '@/lib/status';
import { index, show } from '@/routes/admin/reservations';

type Row = {
    id: number;
    order_code: string;
    status: string;
    customer_name: string;
    customer_phone: string;
    facility: string;
    check_in: string;
    check_out: string;
    guests: number;
    total: number;
};

type Props = {
    reservations: {
        data: Row[];
        prev_page_url: string | null;
        next_page_url: string | null;
        current_page: number;
        last_page: number;
        total: number;
    };
    status: string | null;
    statuses: string[];
};

export default function AdminReservationsIndex({
    reservations,
    status,
    statuses,
}: Props) {
    return (
        <>
            <Head title="Reservasi" />
            <div className="space-y-4 p-4">
                <div className="flex flex-wrap items-center gap-4">
                    <h1 className="mr-auto text-2xl font-semibold">
                        Reservasi
                    </h1>
                    <select
                        aria-label="Filter status"
                        value={status ?? ''}
                        onChange={(event) =>
                            router.get(
                                index.url(),
                                event.target.value
                                    ? { status: event.target.value }
                                    : {},
                                { preserveState: true },
                            )
                        }
                        className="h-9 rounded-md border bg-transparent px-3 text-sm"
                    >
                        <option value="">Semua status</option>
                        {statuses.map((value) => (
                            <option key={value} value={value}>
                                {orderStatusLabels[value] ?? value}
                            </option>
                        ))}
                    </select>
                </div>

                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="p-3">Kode</th>
                                <th className="p-3">Pemesan</th>
                                <th className="p-3">Fasilitas</th>
                                <th className="p-3">Tanggal</th>
                                <th className="p-3">Total</th>
                                <th className="p-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {reservations.data.map((row) => (
                                <tr
                                    key={row.id}
                                    className="border-b last:border-0"
                                >
                                    <td className="p-3">
                                        <Link
                                            href={show(row.id)}
                                            className="font-medium underline"
                                        >
                                            {row.order_code}
                                        </Link>
                                    </td>
                                    <td className="p-3">
                                        {row.customer_name}
                                        <br />
                                        <span className="text-muted-foreground">
                                            {row.customer_phone}
                                        </span>
                                    </td>
                                    <td className="p-3">
                                        {row.facility} · {row.guests} tamu
                                    </td>
                                    <td className="p-3">
                                        {formatDate(row.check_in)} –{' '}
                                        {formatDate(row.check_out)}
                                    </td>
                                    <td className="p-3">
                                        {formatRupiah(row.total)}
                                    </td>
                                    <td className="p-3">
                                        <Badge variant="secondary">
                                            {orderStatusLabels[row.status] ??
                                                row.status}
                                        </Badge>
                                    </td>
                                </tr>
                            ))}
                            {reservations.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="p-6 text-center text-muted-foreground"
                                    >
                                        Belum ada reservasi.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <SimplePagination page={reservations} />
            </div>
        </>
    );
}
