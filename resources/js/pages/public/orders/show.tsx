import { Form, Head } from '@inertiajs/react';
import { Clock, MessageCircle } from 'lucide-react';
import { store } from '@/actions/App/Http/Controllers/Public/PaymentProofController';
import CopyButton from '@/components/site/copy-button';
import FormField from '@/components/site/form-field';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { formatDate, formatDateTime, formatRupiah } from '@/lib/format';
import { useTranslation, type MessageKey } from '@/lib/i18n';
import { cn } from '@/lib/utils';

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

const statusKeys: Record<string, MessageKey> = {
    pending_payment: 'order.status.pending_payment',
    awaiting_verification: 'order.status.awaiting_verification',
    paid: 'order.status.paid',
    confirmed: 'order.status.confirmed',
    rejected: 'order.status.rejected',
    expired: 'order.status.expired',
    cancelled: 'order.status.cancelled',
    refunded: 'order.status.refunded',
};

const statusTones: Record<string, string> = {
    pending_payment: 'bg-amber-100 text-amber-900',
    awaiting_verification: 'bg-sky-100 text-sky-900',
    paid: 'bg-forest-100 text-forest-900',
    confirmed: 'bg-forest-100 text-forest-900',
    rejected: 'bg-red-100 text-red-900',
    expired: 'bg-stone-200 text-stone-800',
    cancelled: 'bg-stone-200 text-stone-800',
    refunded: 'bg-stone-200 text-stone-800',
};

// Where the order is on the normal path; anything else has ended or needs another upload.
const progress: Record<string, number> = {
    pending_payment: 0,
    awaiting_verification: 1,
    paid: 2,
    confirmed: 2,
};

const endedKeys: Record<string, MessageKey> = {
    expired: 'order.ended.expired',
    cancelled: 'order.ended.cancelled',
    refunded: 'order.ended.refunded',
};

const steps: MessageKey[] = [
    'order.step.pay',
    'order.step.verify',
    'order.step.done',
];

export default function OrderShow({
    order,
    payment_methods,
    verification_hours,
}: {
    order: Order;
    payment_methods: PaymentMethod[];
    verification_hours: number;
}) {
    const { t } = useTranslation();
    const statusKey = statusKeys[order.status];
    const step = progress[order.status];
    const endedKey = endedKeys[order.status];

    const shareOnWhatsapp = () => {
        const text = `${t('order.shareText', { code: order.code })} ${window.location.href}`;

        window.open(
            `https://wa.me/?text=${encodeURIComponent(text)}`,
            '_blank',
            'noopener,noreferrer',
        );
    };

    return (
        <>
            <Head title={`${t('order.title')} ${order.code}`}>
                {/* The address carries the access token: keep it out of search results. */}
                <meta name="robots" content="noindex, nofollow" />
            </Head>

            <div className="mx-auto max-w-2xl space-y-6">
                <header>
                    <p className="mb-2 text-xs font-semibold tracking-[0.25em] text-clay-dark uppercase">
                        {t('order.title')}
                    </p>
                    <div className="flex flex-wrap items-center gap-3">
                        <h1 className="font-serif text-3xl font-semibold text-forest-900">
                            {order.code}
                        </h1>
                        <span
                            className={cn(
                                'rounded-full px-3 py-1 text-xs font-semibold',
                                statusTones[order.status] ??
                                    'bg-stone-200 text-stone-800',
                            )}
                        >
                            {statusKey ? t(statusKey) : order.status}
                        </span>
                    </div>
                </header>

                {step !== undefined && (
                    <ol className="grid grid-cols-3 gap-2 text-center text-xs font-medium">
                        {steps.map((key, index) => (
                            <li
                                key={key}
                                aria-current={
                                    index === step ? 'step' : undefined
                                }
                                className={cn(
                                    'rounded-lg px-2 py-2.5',
                                    index <= step
                                        ? 'bg-forest-800 text-cream'
                                        : 'bg-forest-100 text-forest-800',
                                )}
                            >
                                {index + 1}. {t(key)}
                            </li>
                        ))}
                    </ol>
                )}

                {order.status === 'confirmed' && (
                    <p
                        role="status"
                        className="rounded-xl bg-forest-100 p-4 text-sm font-medium text-forest-900"
                    >
                        {t('order.confirmedNote')}
                    </p>
                )}

                {endedKey && (
                    <p
                        role="status"
                        className="rounded-xl bg-stone-200 p-4 text-sm font-medium text-stone-900"
                    >
                        {t(endedKey)}
                    </p>
                )}

                {order.rejection_reason && (
                    <p
                        role="alert"
                        className="rounded-xl bg-red-50 p-4 text-sm font-medium text-red-900"
                    >
                        {t('order.rejected', {
                            reason: order.rejection_reason,
                        })}
                    </p>
                )}

                <section className="rounded-2xl border bg-white p-5 shadow-sm">
                    <h2 className="font-serif text-lg font-semibold text-forest-900">
                        {t('order.saveLinkTitle')}
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {t('order.saveLinkText')}
                    </p>
                    <div className="mt-4 flex flex-wrap gap-2">
                        <CopyButton
                            value={() => window.location.href}
                            label={t('order.copyLink')}
                        />
                        <button
                            type="button"
                            onClick={shareOnWhatsapp}
                            className="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-forest-200 bg-white px-3 text-sm font-medium text-forest-900 transition hover:bg-forest-50 focus-visible:ring-2 focus-visible:ring-forest-600 focus-visible:outline-none"
                        >
                            <MessageCircle
                                className="size-4"
                                aria-hidden="true"
                            />
                            {t('order.shareWhatsapp')}
                        </button>
                    </div>
                </section>

                <section className="rounded-2xl border bg-white p-5 shadow-sm">
                    {order.item && (
                        <div className="mb-4 border-b pb-4 text-sm">
                            <p className="font-semibold text-forest-900">
                                {order.item.title}
                            </p>
                            {order.item.check_in && order.item.check_out && (
                                <p className="mt-1 text-muted-foreground">
                                    {formatDate(order.item.check_in)} –{' '}
                                    {formatDate(order.item.check_out)} ·{' '}
                                    {t('order.guests', {
                                        count: order.item.guests ?? 0,
                                    })}
                                </p>
                            )}
                        </div>
                    )}
                    <dl className="space-y-1.5 text-sm">
                        <div className="flex justify-between gap-4">
                            <dt className="text-muted-foreground">
                                {t('order.subtotal')}
                            </dt>
                            <dd>{formatRupiah(order.subtotal)}</dd>
                        </div>
                        <div className="flex justify-between gap-4">
                            <dt className="text-muted-foreground">
                                {t('order.uniqueCode')}
                            </dt>
                            <dd>{order.unique_code}</dd>
                        </div>
                    </dl>
                    <div className="mt-4 flex flex-wrap items-center justify-between gap-3 border-t pt-4">
                        <div>
                            <p className="text-xs text-muted-foreground">
                                {t('order.total')}
                            </p>
                            <p className="font-serif text-2xl font-semibold text-forest-900">
                                {formatRupiah(order.total)}
                            </p>
                        </div>
                        {order.can_upload_proof && (
                            <CopyButton value={String(order.total)} />
                        )}
                    </div>
                    {order.can_upload_proof && (
                        <p className="mt-2 text-xs text-muted-foreground">
                            {t('order.exactAmount')}
                        </p>
                    )}
                </section>

                {order.can_upload_proof && (
                    <>
                        <section className="rounded-2xl border bg-white p-5 shadow-sm">
                            <h2 className="font-serif text-lg font-semibold text-forest-900">
                                {t('order.payVia')}
                            </h2>
                            {order.expires_at && (
                                <p className="mt-2 flex items-center gap-2 rounded-lg bg-amber-50 px-3 py-2 text-sm font-medium text-amber-900">
                                    <Clock
                                        className="size-4 shrink-0"
                                        aria-hidden="true"
                                    />
                                    {t('order.payBefore', {
                                        time: formatDateTime(order.expires_at),
                                    })}
                                </p>
                            )}
                            <div className="mt-4 space-y-5">
                                {payment_methods.map((method) => (
                                    <div key={method.name + method.type}>
                                        {method.type === 'bank' ? (
                                            <>
                                                <p className="text-sm font-semibold text-forest-900">
                                                    {method.name}
                                                </p>
                                                <div className="mt-1 flex flex-wrap items-center justify-between gap-3">
                                                    <p className="font-mono text-xl tracking-wide text-forest-900">
                                                        {method.account_number}
                                                    </p>
                                                    {method.account_number && (
                                                        <CopyButton
                                                            value={
                                                                method.account_number
                                                            }
                                                        />
                                                    )}
                                                </div>
                                                <p className="mt-1 text-sm text-muted-foreground">
                                                    {t('order.accountHolder', {
                                                        name:
                                                            method.account_holder ??
                                                            '',
                                                    })}
                                                </p>
                                            </>
                                        ) : (
                                            method.qris_url && (
                                                <>
                                                    <p className="text-sm font-semibold text-forest-900">
                                                        {method.name}
                                                    </p>
                                                    <img
                                                        src={method.qris_url}
                                                        alt={method.name}
                                                        className="mt-2 w-full max-w-xs rounded-lg border bg-white object-contain"
                                                    />
                                                </>
                                            )
                                        )}
                                    </div>
                                ))}
                            </div>
                        </section>

                        <section className="rounded-2xl border bg-white p-5 shadow-sm">
                            <h2 className="mb-4 font-serif text-lg font-semibold text-forest-900">
                                {t('order.uploadTitle')}
                            </h2>
                            <Form
                                {...store.form(order.access_token)}
                                className="grid gap-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <FormField
                                            id="proof"
                                            label={t('order.proof')}
                                            error={errors.proof}
                                        >
                                            <Input
                                                id="proof"
                                                name="proof"
                                                type="file"
                                                accept=".jpg,.jpeg,.png,.pdf"
                                                required
                                                className="h-auto py-2"
                                                aria-invalid={
                                                    errors.proof
                                                        ? true
                                                        : undefined
                                                }
                                            />
                                        </FormField>
                                        <FormField
                                            id="sender_name"
                                            label={t('order.sender')}
                                            error={errors.sender_name}
                                        >
                                            <Input
                                                id="sender_name"
                                                name="sender_name"
                                                autoComplete="name"
                                                className="h-11"
                                            />
                                        </FormField>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            className="h-12 w-full text-base"
                                        >
                                            {processing && <Spinner />}
                                            {t('order.sendProof')}
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </section>
                    </>
                )}

                {order.status === 'awaiting_verification' && (
                    <p
                        role="status"
                        className="rounded-xl bg-sky-50 p-4 text-sm font-medium text-sky-900"
                    >
                        {t('order.verifying', { hours: verification_hours })}
                    </p>
                )}
            </div>
        </>
    );
}
