import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatRupiah } from '@/lib/format';
import { show } from '@/routes/facilities';

type Facility = {
    slug: string;
    name: string;
    description: string | null;
    capacity: number | null;
    base_price: number;
};

export default function FacilitiesIndex({
    facilities,
}: {
    facilities: Facility[];
}) {
    return (
        <>
            <Head title="Fasilitas" />
            <h1 className="mb-6 text-2xl font-semibold">Fasilitas</h1>
            <div className="grid gap-4 sm:grid-cols-2">
                {facilities.map((facility) => (
                    <Link key={facility.slug} href={show(facility.slug)}>
                        <Card className="h-full transition hover:border-primary">
                            <CardHeader>
                                <CardTitle>{facility.name}</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-1 text-sm">
                                <p className="text-muted-foreground">
                                    {facility.description}
                                </p>
                                {facility.capacity !== null && (
                                    <p>Kapasitas {facility.capacity} orang</p>
                                )}
                                <p className="font-medium">
                                    Mulai {formatRupiah(facility.base_price)} /
                                    hari
                                </p>
                            </CardContent>
                        </Card>
                    </Link>
                ))}
                {facilities.length === 0 && (
                    <p className="text-muted-foreground">
                        Belum ada fasilitas yang tersedia.
                    </p>
                )}
            </div>
        </>
    );
}
