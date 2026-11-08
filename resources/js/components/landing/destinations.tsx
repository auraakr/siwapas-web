import { MapPin } from 'lucide-react';
import Media from '@/components/landing/media';
import SectionHeading from '@/components/landing/section-heading';
import type { Destination } from '@/types/landing';

export default function Destinations({
    destinations,
}: {
    destinations: Destination[];
}) {
    if (destinations.length === 0) {
        return null;
    }

    return (
        <section id="destinasi" className="scroll-mt-24 bg-cream-dark py-20">
            <div className="mx-auto grid max-w-6xl gap-10 px-4 lg:grid-cols-[1fr_1.5fr]">
                <div className="lg:pt-6">
                    <SectionHeading
                        eyebrow="Destinasi Wisata"
                        title="Jelajahi Pesona Alam & Warisan Pasirnanjung"
                        description="Dari pemandangan alam hingga ruang budaya warga, temukan tempat-tempat yang paling berkesan di desa."
                    />
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                    {destinations.map((destination, position) => (
                        <article
                            key={destination.id}
                            className={`group relative isolate overflow-hidden rounded-2xl ${
                                position % 3 === 0
                                    ? 'aspect-[4/5]'
                                    : 'aspect-[4/4.2]'
                            }`}
                        >
                            <Media
                                src={destination.image}
                                alt={destination.name}
                                className="transition duration-500 group-hover:scale-105"
                            />
                            <div className="absolute inset-0 bg-linear-to-t from-forest-950/85 via-forest-950/10 to-transparent" />
                            <div className="absolute inset-x-0 bottom-0 p-5 text-cream">
                                <h3 className="font-serif text-xl font-semibold">
                                    {destination.name}
                                </h3>
                                {destination.summary && (
                                    <p className="mt-1 line-clamp-2 text-sm text-forest-100">
                                        {destination.summary}
                                    </p>
                                )}
                                {destination.address && (
                                    <p className="mt-2 flex items-center gap-1.5 text-xs text-forest-200">
                                        <MapPin className="size-3.5" />
                                        {destination.address}
                                    </p>
                                )}
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}
