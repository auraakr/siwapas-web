import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatDate, formatDateTime, formatRupiah } from '@/lib/format';
import { store } from '@/actions/App/Http/Controllers/Public/PaymentProofController';

type Order = {
    code: string;
    access_token: string;
    kind: string;
    status: string;
    customer_name: string;
    subtotal: number;
    unique_code: number;
    total: number;
    expires_at: string | null;
    can_upload_proof: boolean;
    rejection_reason: string | null;
    item: {
        title: string;
        check_in?: string;
        check_out?: string;
        guests?: number;
    } | null;
};

type PaymentMethod = {
    type: 'bank' | 'qris';
    name: string;
    account_number: string | null;
    account_holder: string | null;
    qris_url: string | null;
};

const statusLabels: Record<string, string> = {
    pending_payment: 'Menunggu pembayaran',
    awaiting_verification: 'Menunggu verifikasi',
    paid: 'Dibayar',
    confirmed: 'Terkonfirmasi',
    rejected: 'Ditolak',
    expired: 'Kedaluwarsa',
    cancelled: 'Dibatalkan',
    refunded: 'Dikembalikan',
};

export default function OrderShow({
    order,
    payment_methods,
    verification_hours,
}: {
    order: Order;
    payment_methods: PaymentMethod[];
    verification_hours: number;
}) {
    return (
        <>
            <Head title={`Pesanan ${order.code}`} />
            <div className="flex items-center gap-3">
                <h1 className="text-2xl font-semibold">{order.code}</h1>
                <Badge>{statusLabels[order.status] ?? order.status}</Badge>
            </div>
            <p className="mt-1 text-sm text-muted-foreground">
                Simpan tautan halaman ini untuk memantau status pesanan Anda.
            </p>

            {order.item && (
                <div className="mt-6 text-sm">
                    <p className="font-medium">{order.item.title}</p>
                    {order.item.check_in && order.item.check_out && (
                        <p>
                            {formatDate(order.item.check_in)} –{' '}
                            {formatDate(order.item.check_out)} ·{' '}
                            {order.item.guests} tamu
                        </p>
                    )}
                </div>
            )}

            <div className="mt-6 rounded-lg border p-4 text-sm">
                <p>Subtotal: {formatRupiah(order.subtotal)}</p>
                <p>Kode unik: {order.unique_code}</p>
                <p className="mt-1 text-lg font-semibold">
                    Total transfer: {formatRupiah(order.total)}
                </p>
                <p className="text-muted-foreground">
                    Transfer sesuai nominal tepat agar mudah diverifikasi.
                </p>
            </div>

            {order.rejection_reason && (
                <p className="mt-4 text-sm text-red-600">
                    Pembayaran ditolak: {order.rejection_reason}
                </p>
            )}

            {order.can_upload_proof && (
                <>
                    <div className="mt-6 space-y-4 text-sm">
                        <h2 className="font-medium">Bayar melalui</h2>
                        {payment_methods.map((method) => (
                            <div key={method.name + method.type}>
                                {method.type === 'bank' ? (
                                    <>
                                        <p>
                                            {method.name}{' '}
                                            {method.account_number}
                                        </p>
                                        <p>a.n. {method.account_holder}</p>
                                    </>
                                ) : (
                                    method.qris_url && (
                                        <>
                                            <p>{method.name}</p>
                                            <img
                                                src={method.qris_url}
                                                alt={method.name}
                                                className="mt-1 size-56 rounded border object-contain"
                                            />
                                        </>
                                    )
                                )}
                            </div>
                        ))}
                        {order.expires_at && (
                            <p className="text-muted-foreground">
                                Bayar sebelum {formatDateTime(order.expires_at)}
                            </p>
                        )}
                    </div>

                    <h2 className="mt-8 mb-4 text-lg font-medium">
                        Unggah bukti transfer
                    </h2>
                    <Form
                        {...store.form(order.access_token)}
                        className="grid max-w-md gap-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="proof">
                                        Bukti transfer (JPG, PNG, PDF, maks. 5
                                        MB)
                                    </Label>
                                    <Input
                                        id="proof"
                                        name="proof"
                                        type="file"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        required
                                    />
                                    <InputError message={errors.proof} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="sender_name">
                                        Nama pengirim (opsional)
                                    </Label>
                                    <Input
                                        id="sender_name"
                                        name="sender_name"
                                    />
                                    <InputError message={errors.sender_name} />
                                </div>
                                <Button type="submit" disabled={processing}>
                                    {processing && <Spinner />}
                                    Kirim bukti
                                </Button>
                            </>
                        )}
                    </Form>
                </>
            )}

            {order.status === 'awaiting_verification' && (
                <p className="mt-6 text-sm">
                    Bukti transfer diterima. Verifikasi paling lambat{' '}
                    {verification_hours} jam.
                </p>
            )}
        </>
    );
}
