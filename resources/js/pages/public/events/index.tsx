import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDateTime, formatRupiah } from '@/lib/format';
import { show } from '@/routes/events';

type EventSummary = {
    slug: string;
    title: string;
    starts_at: string;
    location_name: string | null;
    fee: number;
};

export default function EventsIndex({ events }: { events: EventSummary[] }) {
    return (
        <>
            <Head title="Acara" />
            <h1 className="mb-6 text-2xl font-semibold">Acara</h1>
            <div className="grid gap-4 sm:grid-cols-2">
                {events.map((event) => (
                    <Link key={event.slug} href={show(event.slug)}>
                        <Card className="h-full transition hover:border-primary">
                            <CardHeader>
                                <CardTitle>{event.title}</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-1 text-sm">
                                <p>{formatDateTime(event.starts_at)}</p>
                                {event.location_name && (
                                    <p>{event.location_name}</p>
                                )}
                                <p className="font-medium">
                                    {event.fee > 0
                                        ? formatRupiah(event.fee)
                                        : 'Gratis'}
                                </p>
                            </CardContent>
                        </Card>
                    </Link>
                ))}
                {events.length === 0 && (
                    <p className="text-muted-foreground">
                        Belum ada acara yang akan datang.
                    </p>
                )}
            </div>
        </>
    );
}
