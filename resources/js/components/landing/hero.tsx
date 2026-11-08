import { Link } from '@inertiajs/react';
import { ChevronDown, Play } from 'lucide-react';
import facilities from '@/routes/facilities';

export default function Hero({
    image,
    videoUrl,
}: {
    image: string | null;
    videoUrl: string | null;
}) {
    return (
        <section className="relative isolate overflow-hidden bg-forest-900 text-cream">
            {image ? (
                <img
                    src={image}
                    alt=""
                    className="absolute inset-0 -z-20 size-full object-cover"
                />
            ) : (
                <div className="absolute inset-0 -z-20 bg-linear-to-br from-forest-950 via-forest-800 to-forest-600" />
            )}
            <div className="absolute inset-0 -z-10 bg-linear-to-t from-forest-950/85 via-forest-950/40 to-forest-950/30" />

            <div className="mx-auto flex min-h-[560px] max-w-6xl flex-col justify-center px-4 py-24 md:min-h-[640px]">
                <p className="mb-5 inline-flex w-fit items-center gap-2 rounded-full border border-cream/30 bg-forest-950/40 px-4 py-1.5 text-[11px] font-semibold tracking-[0.2em] uppercase backdrop-blur">
                    <span className="size-1.5 rounded-full bg-clay" />
                    Wisata Desa Pasirnanjung
                </p>

                <h1 className="font-serif text-5xl leading-[1.05] font-semibold md:text-7xl">
                    Pasirnanjung
                    <span className="block text-forest-200 italic">Geulis</span>
                </h1>

                <p className="mt-6 max-w-xl text-base leading-relaxed text-forest-100 md:text-lg">
                    Menyatu dengan alam, budaya, dan warga desa. Jelajahi
                    destinasi, ikuti kegiatan bersama masyarakat, dan sewa
                    fasilitas desa untuk acaramu.
                </p>

                <div className="mt-8 flex flex-wrap gap-3">
                    <a
                        href="#destinasi"
                        className="rounded-md bg-clay px-6 py-3 text-xs font-semibold tracking-wider text-white uppercase transition hover:bg-clay-dark"
                    >
                        Jelajahi Destinasi
                    </a>
                    <Link
                        href={facilities.index()}
                        className="rounded-md border border-cream/50 px-6 py-3 text-xs font-semibold tracking-wider uppercase backdrop-blur transition hover:bg-cream/10"
                    >
                        Sewa Fasilitas
                    </Link>
                    {videoUrl && (
                        <a
                            href={videoUrl}
                            target="_blank"
                            rel="noreferrer"
                            className="flex items-center gap-2 rounded-md px-4 py-3 text-xs font-semibold tracking-wider uppercase hover:text-clay"
                        >
                            <Play className="size-4" />
                            Tonton Video
                        </a>
                    )}
                </div>
            </div>

            <a
                href="#tentang"
                aria-label="Gulir ke bawah"
                className="absolute bottom-5 left-1/2 -translate-x-1/2 animate-bounce text-cream/70"
            >
                <ChevronDown className="size-6" />
            </a>
        </section>
    );
}
