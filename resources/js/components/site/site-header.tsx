import { Link, usePage } from '@inertiajs/react';
import { Clock, Leaf, Mail, MapPin, Menu, Phone, X } from 'lucide-react';
import { useState } from 'react';
import PublicUserMenu from '@/components/public-user-menu';
import { home, login } from '@/routes';
import events from '@/routes/events';
import facilities from '@/routes/facilities';

const anchor = (hash: string): string => `/#${hash}`;

const navItems = [
    { label: 'Beranda', href: home().url },
    { label: 'Destinasi', href: anchor('destinasi') },
    { label: 'Pengalaman', href: anchor('pengalaman') },
    { label: 'Fasilitas', href: facilities.index().url },
    { label: 'Produk', href: anchor('produk') },
    { label: 'Acara', href: events.index().url },
    { label: 'Kontak', href: anchor('kontak') },
];

export default function SiteHeader() {
    const { auth, site } = usePage().props;
    const contact = site.contact;
    const [open, setOpen] = useState(false);

    const topBarItems = [
        contact.address && { icon: MapPin, text: contact.address },
        contact.hours && { icon: Clock, text: contact.hours },
        contact.phone && { icon: Phone, text: contact.phone },
        contact.email && { icon: Mail, text: contact.email },
    ].filter(Boolean) as { icon: typeof MapPin; text: string }[];

    return (
        <header className="sticky top-0 z-50">
            {topBarItems.length > 0 && (
                <div className="hidden bg-forest-900 text-xs text-forest-100 md:block">
                    <div className="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-1 px-4 py-2">
                        {topBarItems.map(({ icon: Icon, text }) => (
                            <span
                                key={text}
                                className="flex items-center gap-1.5"
                            >
                                <Icon className="size-3.5 opacity-80" />
                                {text}
                            </span>
                        ))}
                    </div>
                </div>
            )}

            <div className="border-b border-forest-100 bg-cream/95 backdrop-blur">
                <div className="mx-auto flex max-w-6xl items-center gap-6 px-4 py-3">
                    <Link href={home()} className="flex items-center gap-3">
                        <span className="flex size-10 items-center justify-center rounded-full bg-forest-800 text-cream">
                            <Leaf className="size-5" />
                        </span>
                        <span className="leading-tight">
                            <span className="block font-serif text-lg font-semibold tracking-wide text-forest-900">
                                SIWAPAS
                            </span>
                            <span className="block text-[10px] tracking-[0.18em] text-forest-700 uppercase">
                                Desa Wisata Pasirnanjung Geulis
                            </span>
                        </span>
                    </Link>

                    <nav className="mx-auto hidden items-center gap-6 text-sm font-medium text-forest-900 lg:flex">
                        {navItems.map((item) => (
                            <Link
                                key={item.label}
                                href={item.href}
                                className="transition hover:text-clay"
                            >
                                {item.label}
                            </Link>
                        ))}
                    </nav>

                    <div className="ml-auto flex items-center gap-3 lg:ml-0">
                        {auth.user ? (
                            <PublicUserMenu user={auth.user} />
                        ) : (
                            <Link
                                href={login()}
                                className="hidden text-sm font-medium text-forest-900 hover:text-clay sm:block"
                            >
                                Masuk
                            </Link>
                        )}
                        <Link
                            href={facilities.index()}
                            className="hidden rounded-md bg-clay px-4 py-2 text-xs font-semibold tracking-wide text-white uppercase transition hover:bg-clay-dark sm:block"
                        >
                            Pesan Sekarang
                        </Link>
                        <button
                            type="button"
                            aria-label={open ? 'Tutup menu' : 'Buka menu'}
                            aria-expanded={open}
                            onClick={() => setOpen(!open)}
                            className="rounded-md p-2 text-forest-900 lg:hidden"
                        >
                            {open ? (
                                <X className="size-6" />
                            ) : (
                                <Menu className="size-6" />
                            )}
                        </button>
                    </div>
                </div>

                {open && (
                    <nav className="border-t border-forest-100 bg-cream px-4 py-3 lg:hidden">
                        <div className="mx-auto flex max-w-6xl flex-col gap-1">
                            {navItems.map((item) => (
                                <Link
                                    key={item.label}
                                    href={item.href}
                                    onClick={() => setOpen(false)}
                                    className="rounded-md px-2 py-2 text-sm font-medium text-forest-900 hover:bg-forest-50"
                                >
                                    {item.label}
                                </Link>
                            ))}
                            {!auth.user && (
                                <Link
                                    href={login()}
                                    className="rounded-md px-2 py-2 text-sm font-medium text-forest-900 hover:bg-forest-50"
                                >
                                    Masuk
                                </Link>
                            )}
                            <Link
                                href={facilities.index()}
                                className="mt-2 rounded-md bg-clay px-4 py-2.5 text-center text-xs font-semibold tracking-wide text-white uppercase"
                            >
                                Pesan Sekarang
                            </Link>
                        </div>
                    </nav>
                )}
            </div>
        </header>
    );
}
