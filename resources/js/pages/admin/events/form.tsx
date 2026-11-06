import { Form, Head, Link } from '@inertiajs/react';
import {
    cancelRegistration,
    store,
    update,
} from '@/actions/App/Http/Controllers/Admin/EventController';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { eventStatusLabels, orderStatusLabels } from '@/lib/status';
import { index } from '@/routes/admin/events';

type EventValues = {
    id: number;
    slug: string;
    title_id: string | null;
    title_en: string | null;
    description_id: string | null;
    description_en: string | null;
    requirements_id: string | null;
    requirements_en: string | null;
    organizer: string | null;
    location_name: string | null;
    address: string | null;
    latitude: string | null;
    longitude: string | null;
    starts_at: string | null;
    ends_at: string | null;
    fee: number;
    quota: number | null;
    registration_opens_at: string | null;
    registration_closes_at: string | null;
    status: string;
};

type Registration = {
    id: number;
    code: string;
    is_active: boolean;
    participant_count: number;
    participants: string[];
    order_code: string | null;
    order_status: string | null;
    contact: string | null;
};

type Props = {
    event: EventValues | null;
    registrations: Registration[];
    statuses: string[];
};

function Field({
    label,
    name,
    error,
    ...props
}: {
    label: string;
    name: string;
    error?: string;
} & React.ComponentProps<'input'>) {
    return (
        <div className="grid gap-2">
            <Label htmlFor={name}>{label}</Label>
            <Input id={name} name={name} {...props} />
            <InputError message={error} />
        </div>
    );
}

export default function AdminEventForm({
    event,
    registrations,
    statuses,
}: Props) {
    const action = event ? update.form(event.id) : store.form();

    return (
        <>
            <Head title={event ? 'Ubah acara' : 'Tambah acara'} />
            <div className="space-y-8 p-4">
                <Link href={index()} className="text-sm underline">
                    ← Semua acara
                </Link>
                <h1 className="text-2xl font-semibold">
                    {event ? 'Ubah acara' : 'Tambah acara'}
                </h1>

                <Form {...action} className="grid max-w-2xl gap-4">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <Field
                                    label="Judul (Indonesia)"
                                    name="title_id"
                                    defaultValue={event?.title_id ?? ''}
                                    error={errors.title_id}
                                    required
                                />
                                <Field
                                    label="Judul (English)"
                                    name="title_en"
                                    defaultValue={event?.title_en ?? ''}
                                    error={errors.title_en}
                                />
                                <Field
                                    label="Deskripsi (Indonesia)"
                                    name="description_id"
                                    defaultValue={event?.description_id ?? ''}
                                    error={errors.description_id}
                                />
                                <Field
                                    label="Deskripsi (English)"
                                    name="description_en"
                                    defaultValue={event?.description_en ?? ''}
                                    error={errors.description_en}
                                />
                                <Field
                                    label="Persyaratan (Indonesia)"
                                    name="requirements_id"
                                    defaultValue={event?.requirements_id ?? ''}
                                    error={errors.requirements_id}
                                />
                                <Field
                                    label="Persyaratan (English)"
                                    name="requirements_en"
                                    defaultValue={event?.requirements_en ?? ''}
                                    error={errors.requirements_en}
                                />
                                <Field
                                    label="Penyelenggara"
                                    name="organizer"
                                    defaultValue={event?.organizer ?? ''}
                                    error={errors.organizer}
                                />
                                <Field
                                    label="Nama lokasi"
                                    name="location_name"
                                    defaultValue={event?.location_name ?? ''}
                                    error={errors.location_name}
                                />
                                <Field
                                    label="Alamat"
                                    name="address"
                                    defaultValue={event?.address ?? ''}
                                    error={errors.address}
                                />
                                <div className="grid grid-cols-2 gap-4">
                                    <Field
                                        label="Latitude"
                                        name="latitude"
                                        defaultValue={event?.latitude ?? ''}
                                        error={errors.latitude}
                                    />
                                    <Field
                                        label="Longitude"
                                        name="longitude"
                                        defaultValue={event?.longitude ?? ''}
                                        error={errors.longitude}
                                    />
                                </div>
                                <Field
                                    label="Mulai (WIB)"
                                    name="starts_at"
                                    type="datetime-local"
                                    defaultValue={event?.starts_at ?? ''}
                                    error={errors.starts_at}
                                    required
                                />
                                <Field
                                    label="Selesai (WIB)"
                                    name="ends_at"
                                    type="datetime-local"
                                    defaultValue={event?.ends_at ?? ''}
                                    error={errors.ends_at}
                                />
                                <Field
                                    label="Pendaftaran dibuka (WIB)"
                                    name="registration_opens_at"
                                    type="datetime-local"
                                    defaultValue={
                                        event?.registration_opens_at ?? ''
                                    }
                                    error={errors.registration_opens_at}
                                />
                                <Field
                                    label="Pendaftaran ditutup (WIB)"
                                    name="registration_closes_at"
                                    type="datetime-local"
                                    defaultValue={
                                        event?.registration_closes_at ?? ''
                                    }
                                    error={errors.registration_closes_at}
                                />
                                <Field
                                    label="Biaya per peserta (Rp, 0 = gratis)"
                                    name="fee"
                                    type="number"
                                    min={0}
                                    defaultValue={event?.fee ?? 0}
                                    error={errors.fee}
                                    required
                                />
                                <Field
                                    label="Kuota (kosong = tanpa batas)"
                                    name="quota"
                                    type="number"
                                    min={1}
                                    defaultValue={event?.quota ?? ''}
                                    error={errors.quota}
                                />
                                <div className="grid gap-2">
                                    <Label htmlFor="status">Status</Label>
                                    <select
                                        id="status"
                                        name="status"
                                        defaultValue={event?.status ?? 'draft'}
                                        className="h-9 rounded-md border bg-transparent px-3 text-sm"
                                    >
                                        {statuses.map((value) => (
                                            <option key={value} value={value}>
                                                {eventStatusLabels[value] ??
                                                    value}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.status} />
                                </div>
                            </div>
                            <Button
                                type="submit"
                                disabled={processing}
                                className="w-fit"
                            >
                                {processing && <Spinner />}
                                Simpan
                            </Button>
                        </>
                    )}
                </Form>

                {event && (
                    <section className="space-y-3">
                        <h2 className="text-lg font-medium">
                            Pendaftar ({registrations.length})
                        </h2>
                        {registrations.length === 0 && (
                            <p className="text-sm text-muted-foreground">
                                Belum ada pendaftar.
                            </p>
                        )}
                        {registrations.map((registration) => (
                            <div
                                key={registration.id}
                                className="rounded-lg border p-3 text-sm"
                            >
                                <p className="font-medium">
                                    {registration.code} ·{' '}
                                    {registration.participant_count} peserta{' '}
                                    <Badge
                                        variant={
                                            registration.is_active
                                                ? 'secondary'
                                                : 'outline'
                                        }
                                    >
                                        {registration.is_active
                                            ? 'Aktif'
                                            : 'Tidak aktif'}
                                    </Badge>
                                </p>
                                <p>{registration.participants.join(', ')}</p>
                                {registration.order_code && (
                                    <p className="text-muted-foreground">
                                        {registration.contact} ·{' '}
                                        {registration.order_code} ·{' '}
                                        {orderStatusLabels[
                                            registration.order_status ?? ''
                                        ] ?? registration.order_status}
                                    </p>
                                )}
                                {registration.is_active && (
                                    <Form
                                        {...cancelRegistration.form({
                                            event: event.id,
                                            registration: registration.id,
                                        })}
                                        className="mt-2 flex max-w-md items-start gap-2"
                                    >
                                        {({ processing, errors }) => (
                                            <>
                                                <div className="grid flex-1 gap-1">
                                                    <Input
                                                        name="reason"
                                                        placeholder="Alasan pembatalan"
                                                        required
                                                    />
                                                    <InputError
                                                        message={errors.reason}
                                                    />
                                                </div>
                                                <Button
                                                    type="submit"
                                                    variant="destructive"
                                                    disabled={processing}
                                                >
                                                    Batalkan
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                )}
                            </div>
                        ))}
                    </section>
                )}
            </div>
        </>
    );
}
