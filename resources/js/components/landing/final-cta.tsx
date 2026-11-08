import { Link } from '@inertiajs/react';
import facilities from '@/routes/facilities';

export default function FinalCta({ whatsappUrl }: { whatsappUrl?: string }) {
    return (
        <section className="bg-forest-800 py-16 text-cream">
            <div className="mx-auto max-w-3xl px-4 text-center">
                <h2 className="font-serif text-3xl font-semibold md:text-4xl">
                    Ayo Berkunjung ke Pasirnanjung
                </h2>
                <p className="mt-4 text-forest-100">
                    Rencanakan kunjunganmu, pesan fasilitas, atau tanyakan
                    langsung kepada pengelola desa.
                </p>
                <div className="mt-8 flex flex-wrap justify-center gap-3">
                    <Link
                        href={facilities.index()}
                        className="rounded-md bg-clay px-7 py-3 text-xs font-semibold tracking-wider text-white uppercase transition hover:bg-clay-dark"
                    >
                        Pesan Fasilitas
                    </Link>
                    {whatsappUrl && (
                        <a
                            href={whatsappUrl}
                            target="_blank"
                            rel="noreferrer"
                            className="rounded-md border border-cream/50 px-7 py-3 text-xs font-semibold tracking-wider uppercase transition hover:bg-cream/10"
                        >
                            Hubungi Kami
                        </a>
                    )}
                </div>
            </div>
        </section>
    );
}
