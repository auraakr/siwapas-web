import { Link, usePage } from '@inertiajs/react';
import { Leaf } from 'lucide-react';
import { home } from '@/routes';
import events from '@/routes/events';
import facilities from '@/routes/facilities';

const exploreLinks = [
    { label: 'Destinasi Wisata', href: '/#destinasi' },
    { label: 'Pengalaman Wisata', href: '/#pengalaman' },
    { label: 'Produk Desa', href: '/#produk' },
    { label: 'Cerita Desa', href: '/#cerita' },
];

export default function SiteFooter() {
    const { site } = usePage().props;
    const contact = site.contact;
    const socials = [
        { label: 'Instagram', href: contact.instagram },
        { label: 'Facebook', href: contact.facebook },
        { label: 'YouTube', href: contact.youtube },
    ].filter((item) => item.href);

    return (
        <footer className="bg-forest-950 text-forest-100">
            <div className="mx-auto grid max-w-6xl gap-10 px-4 py-14 md:grid-cols-2 lg:grid-cols-4">
                <div className="space-y-4">
                    <Link href={home()} className="flex items-center gap-3">
                        <span className="flex size-10 items-center justify-center rounded-full bg-forest-700 text-cream">
                            <Leaf className="size-5" />
                        </span>
                        <span className="font-serif text-xl font-semibold text-cream">
                            SIWAPAS
                        </span>
                    </Link>
                    <p className="text-sm leading-relaxed text-forest-200">
                        Sistem Informasi Wisata Pasirnanjung. Informasi
                        destinasi, pemesanan fasilitas, dan pendaftaran acara
                        Desa Wisata Pasirnanjung Geulis.
                    </p>
                </div>

                <div>
                    <h3 className="mb-4 text-sm font-semibold tracking-wider text-cream uppercase">
                        Jelajah Desa
                    </h3>
                    <ul className="space-y-2 text-sm text-forest-200">
                        {exploreLinks.map((link) => (
                            <li key={link.label}>
                                <Link
                                    href={link.href}
                                    className="hover:text-cream"
                                >
                                    {link.label}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>

                <div>
                    <h3 className="mb-4 text-sm font-semibold tracking-wider text-cream uppercase">
                        Layanan & Informasi
                    </h3>
                    <ul className="space-y-2 text-sm text-forest-200">
                        <li>
                            <Link
                                href={facilities.index()}
                                className="hover:text-cream"
                            >
                                Sewa Fasilitas
                            </Link>
                        </li>
                        <li>
                            <Link
                                href={events.index()}
                                className="hover:text-cream"
                            >
                                Event & Kegiatan
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/#kunjungan"
                                className="hover:text-cream"
                            >
                                Informasi Kunjungan
                            </Link>
                        </li>
                    </ul>
                </div>

                <div id="kontak" className="space-y-3 text-sm text-forest-200">
                    <h3 className="mb-4 text-sm font-semibold tracking-wider text-cream uppercase">
                        Kontak
                    </h3>
                    {contact.address && <p>{contact.address}</p>}
                    {contact.hours && <p>{contact.hours}</p>}
                    {contact.phone && <p>Telepon: {contact.phone}</p>}
                    {contact.whatsapp_url && (
                        <p>
                            <a
                                href={contact.whatsapp_url}
                                target="_blank"
                                rel="noreferrer"
                                className="underline hover:text-cream"
                            >
                                WhatsApp {contact.whatsapp}
                            </a>
                        </p>
                    )}
                    {contact.email && <p>{contact.email}</p>}
                    {socials.length > 0 && (
                        <p className="flex gap-4 pt-1">
                            {socials.map((item) => (
                                <a
                                    key={item.label}
                                    href={item.href}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="underline hover:text-cream"
                                >
                                    {item.label}
                                </a>
                            ))}
                        </p>
                    )}
                    {Object.keys(contact).length === 0 && (
                        <p>Informasi kontak akan segera tersedia.</p>
                    )}
                </div>
            </div>

            <div className="border-t border-forest-800">
                <p className="mx-auto max-w-6xl px-4 py-5 text-xs text-forest-200">
                    © {new Date().getFullYear()} Desa Wisata Pasirnanjung
                    Geulis. Seluruh hak cipta dilindungi.
                </p>
            </div>
        </footer>
    );
}
