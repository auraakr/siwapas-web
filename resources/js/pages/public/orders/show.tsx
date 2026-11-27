import { Form, Head, usePage } from '@inertiajs/react';
import { Check, Clock, Info, MessageCircle } from 'lucide-react';
import { useId, useState } from 'react';
import { store } from '@/actions/App/Http/Controllers/Public/PaymentProofController';
import CopyButton from '@/components/site/copy-button';
import FormField from '@/components/site/form-field';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { formatDate, formatDateTime, formatRupiah } from '@/lib/format';
import { useTranslation, type MessageKey } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { withWhatsappText } from '@/lib/whatsapp';

type Order = {
    code: string;
    access_token: string;
    kind: string;
    status: string;
    customer_name: string;
    payment_plan: 'down_payment' | 'full';
    subtotal: number;
    unique_code: number;
    total: number;
    dp_percentage: number | null;
    dp_amount: number | null;
    remaining_amount: number | null;
    first_payment_amount: number;
    expires_at: string | null;
    can_upload_proof: boolean;
    can_upload_settlement: boolean;
    settlement_closed: boolean;
    settled_at: string | null;
    settlement_method: 'transfer' | 'cash' | null;
    cancelled_by: 'tenant' | 'manager' | null;
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
    dp_paid: 'order.status.dp_paid',
    awaiting_settlement_verification:
        'order.status.awaiting_settlement_verification',
    paid_in_full: 'order.status.paid_in_full',
    rejected: 'order.status.rejected',
    expired: 'order.status.expired',
    cancelled: 'order.status.cancelled',
    refunded: 'order.status.refunded',
};

const statusTones: Record<string, string> = {
    pending_payment: 'bg-amber-100 text-amber-900',
    awaiting_verification: 'bg-sky-100 text-sky-900',
    awaiting_settlement_verification: 'bg-sky-100 text-sky-900',
    paid: 'bg-forest-100 text-forest-900',
    confirmed: 'bg-forest-100 text-forest-900',
    dp_paid: 'bg-forest-100 text-forest-900',
    paid_in_full: 'bg-forest-800 text-cream',
    rejected: 'bg-red-100 text-red-900',
    expired: 'bg-stone-200 text-stone-800',
    cancelled: 'bg-stone-200 text-stone-800',
    refunded: 'bg-stone-200 text-stone-800',
};

// The steps of each payment plan and where each status sits on them; any other status has ended.
const plans: Record<
    Order['payment_plan'],
    { steps: MessageKey[]; progress: Record<string, number> }
> = {
    full: {
        steps: ['order.step.pay', 'order.step.verify', 'order.step.done'],
        progress: {
            pending_payment: 0,
            awaiting_verification: 1,
            paid: 3,
            confirmed: 3,
        },
    },
    down_payment: {
        steps: [
            'order.step.payDp',
            'order.step.verifyDp',
            'order.step.settle',
            'order.step.paidInFull',
        ],
        progress: {
            pending_payment: 0,
            awaiting_verification: 1,
            dp_paid: 2,
            awaiting_settlement_verification: 2,
            paid_in_full: 4,
        },
    },
};

const endedKeys: Record<string, MessageKey> = {
    expired: 'order.ended.expired',
    cancelled: 'order.ended.cancelled',
    refunded: 'order.ended.refunded',
    rejected: 'order.ended.rejected',
};

function Stepper({ steps, current }: { steps: MessageKey[]; current: number }) {
    const { t } = useTranslation();

    return (
        <ol
            className={cn(
                'grid gap-2 text-center text-xs font-medium',
                steps.length === 4
                    ? 'grid-cols-2 sm:grid-cols-4'
                    : 'grid-cols-3',
            )}
        >
            {steps.map((key, index) => {
                const done = index < current;
                const active = index === current;

                return (
                    <li
                        key={key}
                        aria-current={active ? 'step' : undefined}
                        className={cn(
                            'flex items-center justify-center gap-1.5 rounded-lg px-2 py-2.5',
                            done && 'bg-forest-800 text-cream',
                            active && 'bg-clay text-cream',
                            !done && !active && 'bg-forest-100 text-forest-800',
                        )}
                    >
                        {done ? (
                            <Check className="size-3.5" aria-hidden="true" />
                        ) : (
                            <span>{index + 1}.</span>
                        )}
                        {t(key)}
                    </li>
                );
            })}
        </ol>
    );
}

function Notice({
    tone,
    children,
}: {
    tone: 'info' | 'success' | 'warning' | 'danger' | 'muted';
    children: React.ReactNode;
}) {
    const tones = {
        info: 'bg-sky-50 text-sky-900',
        success: 'bg-forest-100 text-forest-900',
        warning: 'bg-amber-50 text-amber-900',
        danger: 'bg-red-50 text-red-900',
        muted: 'bg-stone-200 text-stone-900',
    };

    return (
        <div
            role={tone === 'danger' ? 'alert' : 'status'}
            className={cn('rounded-xl p-4 text-sm font-medium', tones[tone])}
        >
            {children}
        </div>
    );
}

function AmountRow({
    label,
    value,
    strong = false,
}: {
    label: string;
    value: string;
    strong?: boolean;
}) {
    return (
        <div className="flex justify-between gap-4">
            <dt
                className={
                    strong
                        ? 'font-semibold text-forest-900'
                        : 'text-muted-foreground'
                }
            >
                {label}
            </dt>
            <dd
                className={strong ? 'font-semibold text-forest-900' : undefined}
            >
                {value}
            </dd>
        </div>
    );
}

/**
 * The unique code row with an (i) button that opens a short explanation. A tap toggles it, so it
 * works on phones where hover tooltips do not.
 */
function UniqueCodeRow({
    label,
    value,
    help,
}: {
    label: string;
    value: string;
    help: string;
}) {
    const { t } = useTranslation();
    const [open, setOpen] = useState(false);
    const helpId = useId();

    return (
        <div className="flex justify-between gap-4">
            <dt className="text-muted-foreground">
                <span className="inline-flex items-center gap-1">
                    {label}
                    <button
                        type="button"
                        onClick={() => setOpen((isOpen) => !isOpen)}
                        aria-expanded={open}
                        aria-controls={helpId}
                        aria-label={t('order.uniqueCodeWhat')}
                        title={t('order.uniqueCodeWhat')}
                        className="inline-flex size-6 items-center justify-center rounded-full text-clay-dark transition hover:bg-clay/10 focus-visible:ring-2 focus-visible:ring-clay focus-visible:outline-none"
                    >
                        <Info className="size-4" aria-hidden="true" />
                    </button>
                </span>
                <p
                    id={helpId}
                    hidden={!open}
                    className="mt-1.5 rounded-md bg-cream px-3 py-2 text-xs leading-relaxed text-forest-900"
                >
                    {help}
                </p>
            </dt>
            <dd className="shrink-0">{value}</dd>
        </div>
    );
}

function PaymentMethods({ methods }: { methods: PaymentMethod[] }) {
    const { t } = useTranslation();

    return (
        <div className="mt-4 space-y-5">
            {methods.map((method) => (
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
                                    <CopyButton value={method.account_number} />
                                )}
                            </div>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {t('order.accountHolder', {
                                    name: method.account_holder ?? '',
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
    );
}

function UploadProof({ token, title }: { token: string; title: string }) {
    const { t } = useTranslation();

    return (
        <section className="rounded-2xl border bg-white p-5 shadow-sm">
            <h2 className="mb-4 font-serif text-lg font-semibold text-forest-900">
                {title}
            </h2>
            <Form {...store.form(token)} className="grid gap-4">
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
                                aria-invalid={errors.proof ? true : undefined}
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
    );
}

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
    const { site } = usePage().props;
    const plan = plans[order.payment_plan];
    const step = plan.progress[order.status];
    const statusKey = statusKeys[order.status];
    const endedKey = endedKeys[order.status];
    const isDownPayment = order.payment_plan === 'down_payment';
    const remaining = order.remaining_amount ?? 0;
    const managersWhatsapp = site.contact.whatsapp_url
        ? withWhatsappText(
              site.contact.whatsapp_url,
              t('order.askManagerMessage', { code: order.code }),
          )
        : null;

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
                    <Stepper steps={plan.steps} current={step} />
                )}

                {/* What is going on, in plain words. */}
                {order.rejection_reason && (
                    <Notice tone="danger">
                        {t(
                            order.status === 'dp_paid'
                                ? 'order.rejectedSettlement'
                                : 'order.rejected',
                            { reason: order.rejection_reason },
                        )}
                        {order.can_upload_proof && order.expires_at && (
                            <span className="mt-1 block font-normal">
                                {t('order.uploadAgainBefore', {
                                    time: formatDateTime(order.expires_at),
                                })}
                            </span>
                        )}
                    </Notice>
                )}

                {order.status === 'awaiting_verification' && (
                    <Notice tone="info">
                        {t(
                            isDownPayment
                                ? 'order.verifyingDp'
                                : 'order.verifying',
                            { hours: verification_hours },
                        )}
                    </Notice>
                )}

                {order.status === 'confirmed' && (
                    <Notice tone="success">{t('order.confirmedNote')}</Notice>
                )}

                {order.status === 'dp_paid' && (
                    <Notice tone="success">
                        {t('order.dpPaidNote', {
                            amount: formatRupiah(remaining),
                        })}
                    </Notice>
                )}

                {order.status === 'awaiting_settlement_verification' && (
                    <Notice tone="info">
                        {t('order.verifyingSettlement', {
                            hours: verification_hours,
                        })}
                    </Notice>
                )}

                {order.status === 'paid_in_full' && (
                    <Notice tone="success">
                        {t(
                            order.settlement_method === 'cash'
                                ? 'order.paidInFullCash'
                                : 'order.paidInFullNote',
                        )}
                    </Notice>
                )}

                {order.settlement_closed && (
                    <Notice tone="warning">
                        {t('order.settlementClosed', {
                            amount: formatRupiah(remaining),
                        })}
                        {managersWhatsapp && (
                            <a
                                href={managersWhatsapp}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="mt-2 inline-flex items-center gap-1.5 font-semibold underline"
                            >
                                <MessageCircle
                                    className="size-4"
                                    aria-hidden="true"
                                />
                                {t('order.contactManagers')}
                            </a>
                        )}
                    </Notice>
                )}

                {endedKey && (
                    <Notice tone="muted">
                        {t(endedKey)}
                        {order.status === 'cancelled' &&
                            order.cancelled_by === 'tenant' &&
                            isDownPayment && (
                                <span className="mt-1 block font-normal">
                                    {t('order.dpForfeited')}
                                </span>
                            )}
                    </Notice>
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

                    {isDownPayment ? (
                        <dl className="space-y-1.5 text-sm">
                            <AmountRow
                                label={t('order.fullPrice')}
                                value={formatRupiah(order.subtotal)}
                            />
                            <AmountRow
                                label={t('order.dpPart', {
                                    percent: order.dp_percentage ?? 0,
                                })}
                                value={formatRupiah(order.dp_amount ?? 0)}
                            />
                            <UniqueCodeRow
                                label={t('order.uniqueCodeOnDp')}
                                value={`+${order.unique_code}`}
                                help={t('order.uniqueCodeHelpDp')}
                            />
                            <AmountRow
                                label={t('order.remaining')}
                                value={formatRupiah(remaining)}
                            />
                        </dl>
                    ) : (
                        <dl className="space-y-1.5 text-sm">
                            <AmountRow
                                label={t('order.subtotal')}
                                value={formatRupiah(order.subtotal)}
                            />
                            <UniqueCodeRow
                                label={t('order.uniqueCode')}
                                value={`+${order.unique_code}`}
                                help={t('order.uniqueCodeHelp')}
                            />
                        </dl>
                    )}

                    {(order.can_upload_proof ||
                        order.can_upload_settlement) && (
                        <div className="mt-4 border-t pt-4">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p className="text-xs text-muted-foreground">
                                        {t(
                                            order.can_upload_settlement
                                                ? 'order.transferSettlement'
                                                : isDownPayment
                                                  ? 'order.transferDp'
                                                  : 'order.total',
                                        )}
                                    </p>
                                    <p className="font-serif text-2xl font-semibold text-forest-900">
                                        {formatRupiah(
                                            order.can_upload_settlement
                                                ? remaining
                                                : order.first_payment_amount,
                                        )}
                                    </p>
                                </div>
                                <CopyButton
                                    value={String(
                                        order.can_upload_settlement
                                            ? remaining
                                            : order.first_payment_amount,
                                    )}
                                />
                            </div>
                            <p className="mt-2 text-xs text-muted-foreground">
                                {t(
                                    order.can_upload_settlement
                                        ? 'order.settlementExact'
                                        : 'order.exactAmount',
                                )}
                            </p>
                        </div>
                    )}
                </section>

                {(order.can_upload_proof || order.can_upload_settlement) && (
                    <section className="rounded-2xl border bg-white p-5 shadow-sm">
                        <h2 className="font-serif text-lg font-semibold text-forest-900">
                            {t('order.payVia')}
                        </h2>
                        {order.can_upload_proof && order.expires_at && (
                            <p className="mt-2 flex items-center gap-2 rounded-lg bg-amber-50 px-3 py-2 text-sm font-medium text-amber-900">
                                <Clock
                                    className="size-4 shrink-0"
                                    aria-hidden="true"
                                />
                                {t(
                                    isDownPayment
                                        ? 'order.payDpBefore'
                                        : 'order.payBefore',
                                    { time: formatDateTime(order.expires_at) },
                                )}
                            </p>
                        )}
                        {order.can_upload_settlement && (
                            <p className="mt-2 rounded-lg bg-cream px-3 py-2 text-sm text-forest-900">
                                {t('order.settleOptions', {
                                    date: order.item?.check_out
                                        ? formatDate(order.item.check_out)
                                        : '-',
                                })}
                            </p>
                        )}
                        <PaymentMethods methods={payment_methods} />
                    </section>
                )}

                {order.can_upload_proof && (
                    <UploadProof
                        token={order.access_token}
                        title={t(
                            isDownPayment
                                ? 'order.uploadDpTitle'
                                : 'order.uploadTitle',
                        )}
                    />
                )}

                {order.can_upload_settlement && (
                    <UploadProof
                        token={order.access_token}
                        title={t('order.uploadSettlementTitle')}
                    />
                )}
            </div>
        </>
    );
}
