import { Form, Head, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDate, formatDateTime, formatRupiah } from '@/lib/format';
import { orderStatusLabels } from '@/lib/status';
import { cancel, index } from '@/routes/admin/reservations';

type Props = {
    reservation: {
        id: number;
        facility: string;
        check_in: string;
        check_out: string;
        guests: number;
        is_active: boolean;
        price_breakdown: { date: string; price: number; rule: string | null }[];
    };
    order: {
        code: string;
        status: string;
        customer_name: string;
        customer_phone: string;
        customer_email: string | null;
        notes: string | null;
        subtotal: number;
        unique_code: number;
        total: number;
        created_at: string | null;
        confirmed_at: string | null;
        can_cancel: boolean;
    };
    payments: {
        id: number;
        status: string;
        expected_amount: number;
        rejection_reason: string | null;
        proofs: {
            name: string;
            sender_name: string | null;
            claimed_amount: number | null;
            url: string;
        }[];
    }[];
    history: {
        from: string | null;
        to: string;
        actor: string | null;
        note: string | null;
        at: string | null;
    }[];
};

export default function AdminReservationShow({
    reservation,
    order,
    payments,
    history,
}: Props) {
    return (
        <>
            <Head title={`Reservasi ${order.code}`} />
            <div className="space-y-6 p-4">
                <Link href={index()} className="text-sm underline">
                    ← Semua reservasi
                </Link>

                <div className="flex items-center gap-3">
                    <h1 className="text-2xl font-semibold">{order.code}</h1>
                    <Badge>
                        {orderStatusLabels[order.status] ?? order.status}
                    </Badge>
                </div>

                <section className="grid gap-1 text-sm">
                    <h2 className="text-lg font-medium">Reservasi</h2>
                    <p>
                        {reservation.facility} · {reservation.guests} tamu
                    </p>
                    <p>
                        {formatDate(reservation.check_in)} –{' '}
                        {formatDate(reservation.check_out)}
                    </p>
                    <ul className="mt-1 list-disc pl-5 text-muted-foreground">
                        {reservation.price_breakdown.map((night) => (
                            <li key={night.date}>
                                {formatDate(night.date)}:{' '}
                                {formatRupiah(night.price)}
                                {night.rule && ` (${night.rule})`}
                            </li>
                        ))}
                    </ul>
                    <p className="mt-1 font-medium">
                        Total {formatRupiah(order.total)} (subtotal{' '}
                        {formatRupiah(order.subtotal)} + kode unik{' '}
                        {order.unique_code})
                    </p>
                </section>

                <section className="grid gap-1 text-sm">
                    <h2 className="text-lg font-medium">Pemesan</h2>
                    <p>{order.customer_name}</p>
                    <p>{order.customer_phone}</p>
                    {order.customer_email && <p>{order.customer_email}</p>}
                    {order.notes && <p>Catatan: {order.notes}</p>}
                </section>

                <section className="grid gap-2 text-sm">
                    <h2 className="text-lg font-medium">Pembayaran</h2>
                    {payments.map((payment) => (
                        <div key={payment.id} className="rounded-lg border p-3">
                            <p>
                                {formatRupiah(payment.expected_amount)} ·{' '}
                                {payment.status}
                            </p>
                            {payment.rejection_reason && (
                                <p className="text-red-600">
                                    Ditolak: {payment.rejection_reason}
                                </p>
                            )}
                            <ul className="list-disc pl-5">
                                {payment.proofs.map((proof) => (
                                    <li key={proof.url}>
                                        <a
                                            href={proof.url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="underline"
                                        >
                                            {proof.name}
                                        </a>
                                        {proof.sender_name &&
                                            ` · ${proof.sender_name}`}
                                        {proof.claimed_amount !== null &&
                                            ` · ${formatRupiah(proof.claimed_amount)}`}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </section>

                <section className="grid gap-1 text-sm">
                    <h2 className="text-lg font-medium">Riwayat status</h2>
                    <ul className="list-disc pl-5">
                        {history.map((row, position) => (
                            <li key={position}>
                                {row.at && `${formatDateTime(row.at)} · `}
                                {row.from
                                    ? `${orderStatusLabels[row.from] ?? row.from} → `
                                    : ''}
                                {orderStatusLabels[row.to] ?? row.to} (
                                {row.actor}){row.note && ` – ${row.note}`}
                            </li>
                        ))}
                    </ul>
                </section>

                {order.can_cancel && (
                    <Form
                        {...cancel.form(reservation.id)}
                        className="flex max-w-md items-start gap-2"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid flex-1 gap-1">
                                    <Input
                                        name="reason"
                                        placeholder="Alasan pembatalan"
                                        required
                                    />
                                    <InputError message={errors.reason} />
                                </div>
                                <Button
                                    type="submit"
                                    variant="destructive"
                                    disabled={processing}
                                >
                                    Batalkan reservasi
                                </Button>
                            </>
                        )}
                    </Form>
                )}
            </div>
        </>
    );
}
