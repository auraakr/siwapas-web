import { MapPin } from 'lucide-react';
import Media from '@/components/landing/media';
import SectionHeading from '@/components/landing/section-heading';

const highlights = [
    'Pesan tanpa perlu membuat akun',
    'Dikonfirmasi langsung oleh pengelola desa',
    'Pembayaran lewat transfer atau QRIS',
];

export default function About({
    image,
    address,
}: {
    image: string | null;
    address?: string;
}) {
    return (
        <section id="tentang" className="scroll-mt-24 bg-cream py-20">
            <div className="mx-auto grid max-w-6xl items-center gap-12 px-4 lg:grid-cols-2">
                <div>
                    <SectionHeading
                        eyebrow="Tentang Desa"
                        title="Mengenal Desa Wisata Pasirnanjung Geulis"
                    />
                    <div className="mt-6 space-y-4 leading-relaxed text-forest-800/80">
                        <p>
                            Pasirnanjung Geulis adalah desa wisata yang mengajak
                            pengunjung menikmati alam, budaya, dan keseharian
                            warganya. Di sini kamu bisa menjelajahi destinasi
                            alam, mengikuti kegiatan bersama masyarakat, membawa
                            pulang produk lokal, dan menyewa fasilitas desa
                            untuk acara.
                        </p>
                        <p>
                            Lewat situs ini kamu dapat melihat destinasi dan
                            kegiatan, memesan fasilitas, serta mendaftar acara
                            dengan mudah.
                        </p>
                    </div>
                    <ul className="mt-6 space-y-2 text-sm font-medium text-forest-900">
                        {highlights.map((item) => (
                            <li key={item} className="flex items-center gap-2">
                                <span className="size-2 rounded-full bg-clay" />
                                {item}
                            </li>
                        ))}
                    </ul>
                </div>

                <div className="relative mx-auto w-full max-w-md">
                    <div className="aspect-[3/4] overflow-hidden rounded-t-[999px] rounded-b-3xl border-8 border-white shadow-xl">
                        <Media
                            src={image}
                            alt="Suasana Desa Wisata Pasirnanjung Geulis"
                        />
                    </div>
                    {address && (
                        <p className="absolute right-4 -bottom-5 left-4 flex items-center gap-2 rounded-xl bg-white px-4 py-3 text-xs font-medium text-forest-900 shadow-lg">
                            <MapPin className="size-4 shrink-0 text-clay" />
                            {address}
                        </p>
                    )}
                </div>
            </div>
        </section>
    );
}
