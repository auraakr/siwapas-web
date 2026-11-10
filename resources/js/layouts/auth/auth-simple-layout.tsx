import { Link } from '@inertiajs/react';
import { ArrowLeft, Leaf } from 'lucide-react';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

function Brand({ inverted = false }: { inverted?: boolean }) {
    return (
        <Link href={home()} className="flex items-center gap-3">
            <span
                className={`flex size-10 items-center justify-center rounded-full ${
                    inverted
                        ? 'bg-forest-700 text-cream'
                        : 'bg-forest-800 text-cream'
                }`}
            >
                <Leaf className="size-5" />
            </span>
            <span className="leading-tight">
                <span
                    className={`block font-serif text-lg font-semibold tracking-wide ${
                        inverted ? 'text-cream' : 'text-forest-900'
                    }`}
                >
                    SIWAPAS
                </span>
                <span
                    className={`block text-[10px] tracking-[0.18em] uppercase ${
                        inverted ? 'text-forest-200' : 'text-forest-700'
                    }`}
                >
                    Desa Wisata Pasirnanjung Geulis
                </span>
            </span>
        </Link>
    );
}

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="auth-theme grid min-h-svh bg-cream text-forest-950 lg:grid-cols-[1.05fr_1fr]">
            <aside className="relative isolate hidden flex-col justify-between overflow-hidden bg-forest-900 p-12 text-cream lg:flex">
                <div className="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_15%_20%,rgba(47,122,82,0.55),transparent_55%),radial-gradient(circle_at_85%_85%,rgba(196,98,45,0.28),transparent_50%)]" />

                <Brand inverted />

                <div className="max-w-md">
                    <p className="mb-4 text-xs font-semibold tracking-[0.25em] text-clay uppercase">
                        Selamat datang
                    </p>
                    <h2 className="font-serif text-4xl leading-tight font-semibold">
                        Satu akun untuk kunjunganmu ke Pasirnanjung Geulis
                    </h2>
                    <p className="mt-5 leading-relaxed text-forest-100">
                        Masuk atau daftar untuk melanjutkan. Pemesanan fasilitas
                        dan pendaftaran acara tetap bisa dilakukan tanpa akun.
                    </p>
                </div>

                <p className="text-xs text-forest-200">
                    © {new Date().getFullYear()} Desa Wisata Pasirnanjung Geulis
                </p>
            </aside>

            <div className="flex flex-col">
                <div className="flex items-center justify-between p-6 md:px-10">
                    <div className="lg:invisible">
                        <Brand />
                    </div>
                    <Link
                        href={home()}
                        className="flex items-center gap-1.5 text-sm font-medium text-forest-800 hover:text-clay"
                    >
                        <ArrowLeft className="size-4" />
                        Kembali ke beranda
                    </Link>
                </div>

                <div className="flex flex-1 items-center justify-center px-6 pb-12 md:px-10">
                    <div className="w-full max-w-md rounded-2xl border border-forest-100 bg-white p-8 shadow-sm">
                        <div className="mb-8 space-y-2">
                            <h1 className="font-serif text-2xl font-semibold text-forest-900">
                                {title}
                            </h1>
                            {description && (
                                <p className="text-sm text-muted-foreground">
                                    {description}
                                </p>
                            )}
                        </div>
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
}
