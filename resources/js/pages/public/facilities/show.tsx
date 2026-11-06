import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatDate, formatRupiah } from '@/lib/format';
import { store } from '@/actions/App/Http/Controllers/Public/ReservationController';

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
    return (
        <>
            <Head title={facility.name} />
            <h1 className="text-2xl font-semibold">{facility.name}</h1>
            <p className="mt-2 text-muted-foreground">{facility.description}</p>
            <p className="mt-2 text-sm">
                {facility.capacity !== null &&
                    `Kapasitas ${facility.capacity} orang · `}
                Mulai {formatRupiah(facility.base_price)} / hari
            </p>

            {unavailable.length > 0 && (
                <div className="mt-6 text-sm">
                    <h2 className="font-medium">Tanggal yang sudah terisi</h2>
                    <ul className="mt-1 list-disc pl-5 text-muted-foreground">
                        {unavailable.map((range) => (
                            <li key={range.from}>
                                {formatDate(range.from)} –{' '}
                                {formatDate(range.to)}
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            <h2 className="mt-8 mb-4 text-lg font-medium">Reservasi</h2>
            <Form
                {...store.form(facility.slug)}
                className="grid max-w-md gap-4"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="check_in">Check-in</Label>
                                <Input
                                    id="check_in"
                                    name="check_in"
                                    type="date"
                                    required
                                />
                                <InputError message={errors.check_in} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="check_out">Check-out</Label>
                                <Input
                                    id="check_out"
                                    name="check_out"
                                    type="date"
                                    required
                                />
                                <InputError message={errors.check_out} />
                            </div>
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="guests">Jumlah tamu</Label>
                            <Input
                                id="guests"
                                name="guests"
                                type="number"
                                min={1}
                                defaultValue={1}
                                required
                            />
                            <InputError message={errors.guests} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="customer_name">Nama</Label>
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
                        <div className="grid gap-2">
                            <Label htmlFor="notes">Catatan (opsional)</Label>
                            <Input id="notes" name="notes" />
                            <InputError message={errors.notes} />
                        </div>
                        <Button type="submit" disabled={processing}>
                            {processing && <Spinner />}
                            Pesan sekarang
                        </Button>
                    </>
                )}
            </Form>
        </>
    );
}
