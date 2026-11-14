import { CalendarCheck, MessageCircle, ShoppingBag, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useTranslation } from '@/lib/i18n';

type Props = { whatsappUrl?: string | null };

const copy = {
    id: {
        open: 'Chat WhatsApp',
        close: 'Tutup',
        title: 'Ada yang bisa kami bantu?',
        reservation: 'Reservasi & fasilitas',
        product: 'Pembelian produk',
        msgReservation:
            'Halo, saya ingin bertanya tentang reservasi di Desa Wisata Pasirnanjung Geulis.',
        msgProduct:
            'Halo, saya ingin bertanya tentang produk UMKM Pasirnanjung Geulis.',
    },
    en: {
        open: 'Chat on WhatsApp',
        close: 'Close',
        title: 'How can we help?',
        reservation: 'Reservations & facilities',
        product: 'Buy local products',
        msgReservation:
            'Hello, I would like to ask about reservations at Pasirnanjung Geulis Tourism Village.',
        msgProduct:
            'Hello, I would like to ask about local products from Pasirnanjung Geulis.',
    },
};

function withText(url: string, text: string): string {
    try {
        const parsed = new URL(url);
        parsed.searchParams.set('text', text);

        return parsed.toString();
    } catch {
        return url;
    }
}

export default function FloatingWhatsapp({ whatsappUrl }: Props) {
    const { locale } = useTranslation();
    const [open, setOpen] = useState(false);
    const t = copy[locale === 'en' ? 'en' : 'id'];

    useEffect(() => {
        if (!open) {
            return;
        }

        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        };

        window.addEventListener('keydown', onKeyDown);

        return () => window.removeEventListener('keydown', onKeyDown);
    }, [open]);

    if (!whatsappUrl) {
        return null;
    }

    const options = [
        {
            label: t.reservation,
            href: withText(whatsappUrl, t.msgReservation),
            Icon: CalendarCheck,
        },
        {
            label: t.product,
            href: withText(whatsappUrl, t.msgProduct),
            Icon: ShoppingBag,
        },
    ];

    return (
        <div className="fixed right-4 bottom-[max(1rem,env(safe-area-inset-bottom))] z-50 flex flex-col items-end gap-3">
            {open && (
                <div className="w-64 rounded-2xl border border-forest-200 bg-white p-4 shadow-xl">
                    <p className="mb-3 text-sm font-semibold text-forest-900">
                        {t.title}
                    </p>
                    <ul className="space-y-2">
                        {options.map(({ label, href, Icon }) => (
                            <li key={label}>
                                <a
                                    href={href}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="flex items-center gap-3 rounded-xl bg-cream px-3 py-2.5 text-sm font-medium text-forest-900 transition hover:bg-forest-100"
                                >
                                    <Icon
                                        className="size-4 text-forest-700"
                                        aria-hidden="true"
                                    />
                                    {label}
                                </a>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            <button
                type="button"
                onClick={() => setOpen((value) => !value)}
                aria-expanded={open}
                aria-label={open ? t.close : t.open}
                className="flex items-center gap-2 rounded-full bg-forest-700 px-4 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-forest-800 focus-visible:ring-2 focus-visible:ring-clay focus-visible:ring-offset-2 focus-visible:outline-none"
            >
                {open ? (
                    <X className="size-5" aria-hidden="true" />
                ) : (
                    <MessageCircle className="size-5" aria-hidden="true" />
                )}
                <span className="hidden sm:inline">
                    {open ? t.close : t.open}
                </span>
            </button>
        </div>
    );
}