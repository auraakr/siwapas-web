import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import { store } from '@/actions/App/Http/Controllers/Public/EventRegistrationController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatDateTime, formatRupiah } from '@/lib/format';

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

export default function EventShow({ event, registration_code }: Props) {
    const [participantCount, setParticipantCount] = useState(1);

    return (
        <>
            <Head title={event.title} />
            <h1 className="text-2xl font-semibold">{event.title}</h1>
            <p className="mt-2 text-muted-foreground">{event.description}</p>
            <div className="mt-4 space-y-1 text-sm">
                <p>{formatDateTime(event.starts_at)}</p>
                {event.location_name && <p>{event.location_name}</p>}
                {event.address && <p>{event.address}</p>}
                <p className="font-medium">
                    {event.fee > 0
                        ? `${formatRupiah(event.fee)} / peserta`
                        : 'Gratis'}
                </p>
                {event.remaining_quota !== null && (
                    <p>Sisa kuota: {event.remaining_quota}</p>
                )}
                {event.requirements && <p>Persyaratan: {event.requirements}</p>}
            </div>

            {registration_code && (
                <p className="mt-6 rounded-lg border p-4 text-sm">
                    Pendaftaran berhasil. Kode pendaftaran Anda:{' '}
                    <strong>{registration_code}</strong>
                </p>
            )}

            <h2 className="mt-8 mb-4 text-lg font-medium">Daftar</h2>
            <Form {...store.form(event.slug)} className="grid max-w-md gap-4">
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-2">
                            <Label htmlFor="customer_name">
                                Nama penanggung jawab
                            </Label>
                            <Input
                                id="customer_name"
                                name="customer_name"
                                required
                            />
                            <InputError message={errors.customer_name} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="customer_phone">
                                Nomor telepon
                            </Label>
                            <Input
                                id="customer_phone"
                                name="customer_phone"
                                type="tel"
                                placeholder="08xxxxxxxxxx"
                                required
                            />
                            <InputError message={errors.customer_phone} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="customer_email">
                                Email (opsional)
                            </Label>
                            <Input
                                id="customer_email"
                                name="customer_email"
                                type="email"
                            />
                            <InputError message={errors.customer_email} />
                        </div>

                        {Array.from(
                            { length: participantCount },
                            (_, index) => (
                                <div key={index} className="grid gap-2">
                                    <Label htmlFor={`participant-${index}`}>
                                        Peserta {index + 1}
                                    </Label>
                                    <Input
                                        id={`participant-${index}`}
                                        name={`participants[${index}][name]`}
                                        required
                                    />
                                    <InputError
                                        message={
                                            errors[`participants.${index}.name`]
                                        }
                                    />
                                </div>
                            ),
                        )}
                        <InputError message={errors.participants} />

                        <div className="flex gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                disabled={participantCount >= 10}
                                onClick={() =>
                                    setParticipantCount(participantCount + 1)
                                }
                            >
                                Tambah peserta
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing && <Spinner />}
                                Daftar
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}
