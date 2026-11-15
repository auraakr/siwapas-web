import { Form, Head } from '@inertiajs/react';
import { CalendarDays, MapPin, Ticket, Users } from 'lucide-react';
import { useState } from 'react';
import { store } from '@/actions/App/Http/Controllers/Public/EventRegistrationController';
import FormField from '@/components/site/form-field';
import PageTitle from '@/components/site/page-title';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { formatDateTime, formatRupiah } from '@/lib/format';
import { useTranslation } from '@/lib/i18n';

type Props = {
    event: {
        slug: string;
        title: string;
        description: string | null;
        organizer: string | null;
        location_name: string | null;
        address: string | null;
        starts_at: string;
        ends_at: string | null;
        fee: number;
        requirements: string | null;
        remaining_quota: number | null;
    };
    registration_code: string | null;
};

const MAX_PARTICIPANTS = 10;

export default function EventShow({ event, registration_code }: Props) {
    const { t } = useTranslation();
    const [participantCount, setParticipantCount] = useState(1);

    const isFull = event.remaining_quota === 0;
    const maxParticipants = Math.min(
        MAX_PARTICIPANTS,
        event.remaining_quota ?? MAX_PARTICIPANTS,
    );
    const mapQuery = [event.location_name, event.address]
        .filter(Boolean)
        .join(', ');
    const description = event.description ?? t('events.pageDescription');

    return (
        <>
            <Head title={event.title}>
                <meta name="description" content={description} />
            </Head>

            <PageTitle eyebrow={t('events.eyebrow')} title={event.title} />

            <div className="grid gap-8 lg:grid-cols-[1.3fr_1fr] lg:items-start">
                <div className="space-y-6">
                    <ul className="space-y-3 rounded-2xl border bg-white p-5 text-sm shadow-sm">
                        <li className="flex gap-3">
                            <CalendarDays
                                className="mt-0.5 size-4 shrink-0 text-clay-dark"
                                aria-hidden="true"
                            />
                            <span>
                                {formatDateTime(event.starts_at)}
                                {event.ends_at &&
                                    ` – ${formatDateTime(event.ends_at)}`}
                            </span>
                        </li>
                        {mapQuery && (
                            <li className="flex gap-3">
                                <MapPin
                                    className="mt-0.5 size-4 shrink-0 text-clay-dark"
                                    aria-hidden="true"
                                />
                                <span>
                                    {event.location_name && (
                                        <span className="block font-medium">
                                            {event.location_name}
                                        </span>
                                    )}
                                    {event.address && (
                                        <span className="block text-muted-foreground">
                                            {event.address}
                                        </span>
                                    )}
                                    <a
                                        href={`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(mapQuery)}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="mt-1 inline-block font-semibold text-clay-dark underline underline-offset-4"
                                    >
                                        {t('destinations.openMap')}
                                    </a>
                                </span>
                            </li>
                        )}
                        <li className="flex gap-3">
                            <Ticket
                                className="mt-0.5 size-4 shrink-0 text-clay-dark"
                                aria-hidden="true"
                            />
                            <span className="font-semibold text-forest-900">
                                {event.fee > 0
                                    ? t('events.perPerson', {
                                          price: formatRupiah(event.fee),
                                      })
                                    : t('events.free')}
                            </span>
                        </li>
                        {event.remaining_quota !== null && (
                            <li className="flex gap-3">
                                <Users
                                    className="mt-0.5 size-4 shrink-0 text-clay-dark"
                                    aria-hidden="true"
                                />
                                <span>
                                    {t('events.quotaLeft', {
                                        count: event.remaining_quota,
                                    })}
                                </span>
                            </li>
                        )}
                    </ul>

                    {event.organizer && (
                        <p className="text-sm text-muted-foreground">
                            {t('events.organizer', { name: event.organizer })}
                        </p>
                    )}
                    {event.description && (
                        <p className="leading-relaxed whitespace-pre-line text-forest-800/90">
                            {event.description}
                        </p>
                    )}
                </div>

                <div
                    id="daftar"
                    className="scroll-mt-28 rounded-2xl border bg-white p-6 shadow-sm lg:sticky lg:top-32 lg:col-start-2 lg:row-span-2 lg:row-start-1"
                >
                    <h2 className="mb-4 font-serif text-xl font-semibold text-forest-900">
                        {t('events.formTitle')}
                    </h2>

                    {registration_code && (
                        <p
                            role="status"
                            className="mb-5 rounded-lg bg-forest-100 p-4 text-sm text-forest-900"
                        >
                            {t('events.success')}{' '}
                            <strong className="font-mono text-base">
                                {registration_code}
                            </strong>
                        </p>
                    )}

                    {isFull ? (
                        <p className="rounded-lg bg-cream p-4 text-sm font-medium text-forest-900">
                            {t('events.full')}
                        </p>
                    ) : (
                        <Form
                            {...store.form(event.slug)}
                            className="grid gap-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <FormField
                                        id="customer_name"
                                        label={t('events.responsible')}
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

                                    {Array.from(
                                        { length: participantCount },
                                        (_, index) => (
                                            <FormField
                                                key={index}
                                                id={`participant-${index}`}
                                                label={t('events.participant', {
                                                    number: index + 1,
                                                })}
                                                error={
                                                    errors[
                                                        `participants.${index}.name`
                                                    ]
                                                }
                                            >
                                                <Input
                                                    id={`participant-${index}`}
                                                    name={`participants[${index}][name]`}
                                                    required
                                                    className="h-11"
                                                    aria-invalid={
                                                        errors[
                                                            `participants.${index}.name`
                                                        ]
                                                            ? true
                                                            : undefined
                                                    }
                                                />
                                            </FormField>
                                        ),
                                    )}
                                    {errors.participants && (
                                        <p className="text-sm text-destructive">
                                            {errors.participants}
                                        </p>
                                    )}

                                    <div className="flex flex-wrap gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            className="h-10"
                                            disabled={
                                                participantCount >=
                                                maxParticipants
                                            }
                                            onClick={() =>
                                                setParticipantCount(
                                                    participantCount + 1,
                                                )
                                            }
                                        >
                                            {t('events.addParticipant')}
                                        </Button>
                                        {participantCount > 1 && (
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                className="h-10"
                                                onClick={() =>
                                                    setParticipantCount(
                                                        participantCount - 1,
                                                    )
                                                }
                                            >
                                                {t('events.removeParticipant')}
                                            </Button>
                                        )}
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        {t('events.participantMax', {
                                            count: maxParticipants,
                                        })}
                                    </p>

                                    {event.fee > 0 && (
                                        <p className="rounded-lg bg-cream p-3 text-sm font-medium text-forest-900">
                                            {t('events.subtotal', {
                                                amount: formatRupiah(
                                                    event.fee *
                                                        participantCount,
                                                ),
                                            })}
                                        </p>
                                    )}

                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        className="h-12 w-full text-base"
                                    >
                                        {processing && <Spinner />}
                                        {t('events.register')}
                                    </Button>
                                </>
                            )}
                        </Form>
                    )}
                </div>

                {event.requirements && (
                    <div>
                        <h2 className="mb-3 font-serif text-xl font-semibold text-forest-900">
                            {t('events.requirements')}
                        </h2>
                        <p className="leading-relaxed whitespace-pre-line text-forest-800/90">
                            {event.requirements}
                        </p>
                    </div>
                )}
            </div>
        </>
    );
}
