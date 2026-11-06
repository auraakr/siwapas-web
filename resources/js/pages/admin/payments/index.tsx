import { Form, Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatRupiah } from '@/lib/format';
import {
    reject,
    verify,
} from '@/actions/App/Http/Controllers/Admin/PaymentController';

type Payment = {
    id: number;
    order_code: string;
    customer_name: string;
    customer_phone: string;
    expected_amount: number;
    proofs: {
        name: string;
        sender_name: string | null;
        claimed_amount: number | null;
        url: string;
    }[];
};

export default function AdminPaymentsIndex({
    payments,
}: {
    payments: Payment[];
}) {
    return (
        <>
            <Head title="Verifikasi pembayaran" />
            <div className="space-y-4 p-4">
                <h1 className="text-2xl font-semibold">
                    Verifikasi pembayaran
                </h1>

                {payments.length === 0 && (
                    <p className="text-muted-foreground">
                        Tidak ada pembayaran yang menunggu verifikasi.
                    </p>
                )}

                {payments.map((payment) => (
                    <div key={payment.id} className="rounded-lg border p-4">
                        <p className="font-medium">
                            {payment.order_code} · {payment.customer_name} (
                            {payment.customer_phone})
                        </p>
                        <p className="text-sm">
                            Nominal seharusnya:{' '}
                            {formatRupiah(payment.expected_amount)}
                        </p>
                        <ul className="mt-2 list-disc pl-5 text-sm">
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

                        <div className="mt-4 flex flex-wrap items-start gap-4">
                            <Form {...verify.form(payment.id)}>
                                {({ processing }) => (
                                    <Button type="submit" disabled={processing}>
                                        Verifikasi
                                    </Button>
                                )}
                            </Form>
                            <Form
                                {...reject.form(payment.id)}
                                className="flex gap-2"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div>
                                            <Input
                                                name="reason"
                                                placeholder="Alasan penolakan"
                                                required
                                            />
                                            {errors.reason && (
                                                <p className="text-sm text-red-600">
                                                    {errors.reason}
                                                </p>
                                            )}
                                        </div>
                                        <Button
                                            type="submit"
                                            variant="destructive"
                                            disabled={processing}
                                        >
                                            Tolak
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </div>
                    </div>
                ))}
            </div>
        </>
    );
}
