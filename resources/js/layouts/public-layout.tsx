import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import PublicUserMenu from '@/components/public-user-menu';
import { home, login, register } from '@/routes';
import events from '@/routes/events';
import facilities from '@/routes/facilities';

export default function PublicLayout({ children }: { children: ReactNode }) {
    const { auth } = usePage().props;

    return (
        <div className="min-h-screen bg-background text-foreground">
            <header className="border-b">
                <nav className="mx-auto flex max-w-4xl items-center gap-6 px-4 py-4 text-sm">
                    <Link href={home()} className="mr-auto font-semibold">
                        SIWAPAS
                    </Link>
                    <Link href={facilities.index()}>Fasilitas</Link>
                    <Link href={events.index()}>Acara</Link>
                    {auth.user ? (
                        <PublicUserMenu user={auth.user} />
                    ) : (
                        <>
                            <Link href={login()}>Masuk</Link>
                            <Link href={register()}>Daftar</Link>
                        </>
                    )}
                </nav>
            </header>
            <main className="mx-auto max-w-4xl px-4 py-8">{children}</main>
        </div>
    );
}
