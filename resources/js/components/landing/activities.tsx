import { MapPin } from 'lucide-react';
import Media from '@/components/landing/media';
import SectionHeading from '@/components/landing/section-heading';
import type { Destination } from '@/types/landing';

export default function Activities({
    activities,
    whatsappUrl,
}: {
    activities: Destination[];
    whatsappUrl?: string;
}) {
    if (activities.length === 0) {
        return null;
    }

    return (
        <section id="pengalaman" className="scroll-mt-24 bg-cream py-20">
            <div className="mx-auto max-w-6xl px-4">
                <SectionHeading
                    centered
                    eyebrow="Pengalaman Wisata"
                    title="Temukan Pengalamanmu di Desa"
                    description="Kegiatan seru bersama warga yang bisa kamu ikuti selama berkunjung."
                />

                <div className="mt-12 grid gap-6 md:grid-cols-3">
                    {activities.map((activity) => (
                        <article
                            key={activity.id}
                            className="flex flex-col overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm"
                        >
                            <div className="relative aspect-[4/3]">
                                <Media
                                    src={activity.image}
                                    alt={activity.name}
                                />
                                <span className="absolute top-3 left-3 rounded-full bg-forest-900/85 px-3 py-1 text-[10px] font-semibold tracking-wider text-cream uppercase">
                                    {activity.requires_reservation
                                        ? 'Perlu reservasi'
                                        : 'Tanpa reservasi'}
                                </span>
                            </div>
                            <div className="flex flex-1 flex-col p-5">
                                <h3 className="font-serif text-xl font-semibold text-forest-900">
                                    {activity.name}
                                </h3>
                                {activity.summary && (
                                    <p className="mt-2 line-clamp-3 text-sm leading-relaxed text-forest-800/80">
                                        {activity.summary}
                                    </p>
                                )}
                                {activity.address && (
                                    <p className="mt-3 flex items-center gap-1.5 text-xs text-forest-700">
                                        <MapPin className="size-3.5" />
                                        {activity.address}
                                    </p>
                                )}
                                {whatsappUrl && (
                                    <a
                                        href={`${whatsappUrl}?text=${encodeURIComponent(`Halo, saya ingin bertanya tentang ${activity.name}.`)}`}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="mt-auto inline-block self-start rounded-md bg-clay px-4 py-2 text-xs font-semibold tracking-wide text-white uppercase transition hover:bg-clay-dark"
                                    >
                                        Tanya pengelola
                                    </a>
                                )}
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}
