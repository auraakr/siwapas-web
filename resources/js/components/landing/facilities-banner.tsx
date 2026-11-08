import { Link } from '@inertiajs/react';
import SectionHeading from '@/components/landing/section-heading';
import { formatRupiah } from '@/lib/format';
import facilities from '@/routes/facilities';
import type { FacilitySummary } from '@/types/landing';

export default function FacilitiesBanner({
    items,
}: {
    items: FacilitySummary[];
}) {
    return (
        <section
            id="fasilitas"
            className="relative isolate scroll-mt-24 overflow-hidden bg-forest-900 py-20 text-cream"
        >
            <div className="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_20%_20%,rgba(47,122,82,0.5),transparent_55%),radial-gradient(circle_at_85%_80%,rgba(196,98,45,0.25),transparent_50%)]" />
            <div className="mx-auto max-w-4xl px-4 text-center">
                <SectionHeading
                    centered
                    inverted
                    eyebrow="Layanan Desa"
                    title="Sewa Fasilitas Desa Wisata"
                    description="Gunakan fasilitas desa untuk pertemuan, acara keluarga, atau kegiatan kelompokmu. Pilih tanggal, kirim pesanan, dan pengelola akan mengonfirmasinya setelah bukti pembayaran diverifikasi."
                />

                {items.length > 0 && (
                    <ul className="mt-8 flex flex-wrap justify-center gap-3">
                        {items.map((facility) => (
                            <li key={facility.slug}>
                                <Link
                                    href={facilities.show(facility.slug)}
                                    className="block rounded-full border border-cream/30 bg-forest-950/40 px-4 py-2 text-sm backdrop-blur transition hover:border-clay hover:bg-forest-950/70"
                                >
                                    {facility.name}
                                    <span className="ml-2 text-forest-200">
                                        mulai{' '}
                                        {formatRupiah(facility.base_price)} /
                                        hari
                                    </span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}

                <Link
                    href={facilities.index()}
                    className="mt-10 inline-block rounded-md bg-clay px-8 py-3.5 text-xs font-semibold tracking-wider text-white uppercase transition hover:bg-clay-dark"
                >
                    Pesan Fasilitas
                </Link>
            </div>
        </section>
    );
}
