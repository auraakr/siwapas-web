import { Form, Head } from '@inertiajs/react';
import { CalendarX2, Users } from 'lucide-react';
import { useState } from 'react';
import { store } from '@/actions/App/Http/Controllers/Public/ReservationController';
import FormField from '@/components/site/form-field';
import PageTitle from '@/components/site/page-title';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { formatDate, formatRupiah } from '@/lib/format';
import { useTranslation } from '@/lib/i18n';

type Props = {
    facility: {
        slug: string;
        name: string;
        description: string | null;
        capacity: number | null;
        base_price: number;
        rules: string[] | null;
    };
    unavailable: { from: string; to: string }[];
};

export default function FacilityShow({ facility, unavailable }: Props) {
    const { t } = useTranslation();
    const [checkIn, setCheckIn] = useState('');

    return (
        <>
            <Head title={facility.name}>
                <meta
                    name="description"
                    content={
                        facility.description ?? t('facilities.description')
                    }
                />
            </Head>

            <PageTitle
                eyebrow={t('facilities.eyebrow')}
                title={facility.name}
            />

            <div className="grid gap-8 lg:grid-cols-[1.3fr_1fr] lg:items-start">
                <div className="space-y-5">
                    <div className="flex flex-wrap gap-3">
                        <div className="rounded-xl border bg-white px-5 py-4 shadow-sm">
                            <p className="text-xs text-muted-foreground">
                                {t('facilities.priceFrom')}
                            </p>
                            <p className="font-serif text-2xl font-semibold text-forest-900">
                                {formatRupiah(facility.base_price)}
                                <span className="ml-1 text-sm font-normal text-muted-foreground">
                                    {t('facilities.perDay')}
                                </span>
                            </p>
                        </div>
                        {facility.capacity !== null && (
                            <div className="flex items-center gap-2 rounded-xl border bg-white px-5 py-4 text-sm font-medium shadow-sm">
                                <Users
                                    className="size-4 text-clay-dark"
                                    aria-hidden="true"
                                />
                                {t('facilities.capacity', {
                                    count: facility.capacity,
                                })}
                            </div>
                        )}
                    </div>
                    <p className="text-xs text-muted-foreground">
                        {t('booking.priceNote')}
                    </p>
                    {facility.description && (
                        <p className="leading-relaxed whitespace-pre-line text-forest-800/90">
                            {facility.description}
                        </p>
                    )}
                </div>

                <div
                    id="reservasi"
                    className="scroll-mt-28 rounded-2xl border bg-white p-6 shadow-sm lg:sticky lg:top-32 lg:col-start-2 lg:row-span-2 lg:row-start-1"
                >
                    <h2 className="mb-4 font-serif text-xl font-semibold text-forest-900">
                        {t('booking.formTitle')}
                    </h2>

                    {unavailable.length > 0 && (
                        <div className="mb-5 rounded-lg bg-cream p-4 text-sm">
                            <h3 className="mb-2 flex items-center gap-2 font-semibold text-forest-900">
                                <CalendarX2
                                    className="size-4 text-clay-dark"
                                    aria-hidden="true"
                                />
                                {t('booking.unavailableTitle')}
                            </h3>
                            <ul className="max-h-32 space-y-1 overflow-y-auto text-forest-800/90">
                                {unavailable.map((range) => (
                                    <li key={`${range.from}-${range.to}`}>
                                        {formatDate(range.from)} –{' '}
                                        {formatDate(range.to)}
                                    </li>
                                ))}
                            </ul>
                            <p className="mt-2 text-xs text-muted-foreground">
                                {t('booking.unavailableNote')}
                            </p>
                        </div>
                    )}

                    <Form {...store.form(facility.slug)} className="grid gap-4">
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                                    <FormField
                                        id="check_in"
                                        label={t('booking.checkIn')}
                                        error={errors.check_in}
                                    >
                                        <Input
                                            id="check_in"
                                            name="check_in"
                                            type="date"
                                            required
                                            className="h-11"
                                            aria-invalid={
                                                errors.check_in
                                                    ? true
                                                    : undefined
                                            }
                                            onChange={(event) =>
                                                setCheckIn(event.target.value)
                                            }
                                        />
                                    </FormField>
                                    <FormField
                                        id="check_out"
                                        label={t('booking.checkOut')}
                                        error={errors.check_out}
                                    >
                                        <Input
                                            id="check_out"
                                            name="check_out"
                                            type="date"
                                            required
                                            min={checkIn || undefined}
                                            className="h-11"
                                            aria-invalid={
                                                errors.check_out
                                                    ? true
                                                    : undefined
                                            }
                                        />
                                    </FormField>
                                </div>
                                <FormField
                                    id="guests"
                                    label={t('booking.guests')}
                                    error={errors.guests}
                                >
                                    <Input
                                        id="guests"
                                        name="guests"
                                        type="number"
                                        inputMode="numeric"
                                        min={1}
                                        max={facility.capacity ?? undefined}
                                        defaultValue={1}
                                        required
                                        className="h-11"
                                        aria-invalid={
                                            errors.guests ? true : undefined
                                        }
                                    />
                                </FormField>
                                <FormField
                                    id="customer_name"
                                    label={t('booking.name')}
                                    error={errors.customer_name}
                                >
                                    <Input
                                        id="customer_name"
                                        name="customer_name"
                                        autoComplete="name"
                                        required
                                        className="h-11"
                                        aria-invalid={
                                            errors.customer_name
                                                ? true
                                                : undefined
                                        }
                                    />
                                </FormField>
                                <FormField
                                    id="customer_phone"
                                    label={t('booking.phone')}
                                    error={errors.customer_phone}
                                    hint={t('booking.phoneHint')}
                                >
                                    <Input
                                        id="customer_phone"
                                        name="customer_phone"
                                        type="tel"
                                        inputMode="tel"
                                        autoComplete="tel"
                                        placeholder="08xxxxxxxxxx"
                                        required
                                        className="h-11"
                                        aria-invalid={
                                            errors.customer_phone
                                                ? true
                                                : undefined
                                        }
                                    />
                                </FormField>
                                <FormField
                                    id="customer_email"
                                    label={t('booking.email')}
                                    error={errors.customer_email}
                                >
                                    <Input
                                        id="customer_email"
                                        name="customer_email"
                                        type="email"
                                        inputMode="email"
                                        autoComplete="email"
                                        className="h-11"
                                        aria-invalid={
                                            errors.customer_email
                                                ? true
                                                : undefined
                                        }
                                    />
                                </FormField>
                                <FormField
                                    id="notes"
                                    label={t('booking.notes')}
                                    error={errors.notes}
                                >
                                    <Input
                                        id="notes"
                                        name="notes"
                                        className="h-11"
                                    />
                                </FormField>
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="h-12 w-full text-base"
                                >
                                    {processing && <Spinner />}
                                    {t('booking.submit')}
                                </Button>
                                <p className="text-center text-xs text-muted-foreground">
                                    {t('booking.afterSubmit')}
                                </p>
                            </>
                        )}
                    </Form>
                </div>

                {facility.rules && facility.rules.length > 0 && (
                    <div>
                        <h2 className="mb-3 font-serif text-xl font-semibold text-forest-900">
                            {t('booking.rules')}
                        </h2>
                        <ul className="list-disc space-y-1.5 pl-5 text-forest-800/90">
                            {facility.rules.map((rule) => (
                                <li key={rule}>{rule}</li>
                            ))}
                        </ul>
                    </div>
                )}
            </div>
        </>
    );
}
