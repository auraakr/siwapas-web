import { usePage } from '@inertiajs/react';

export type Locale = 'id' | 'en';

const id = {
    // Navigation
    'nav.home': 'Beranda',
    'nav.explore': 'Jelajahi',
    'nav.explore.tourism': 'Wisata',
    'nav.explore.village': 'Karya Desa',
    'nav.destinations': 'Destinasi',
    'nav.activities': 'Pengalaman Wisata',
    'nav.products': 'Produk Desa',
    'nav.stories': 'Cerita Desa',
    'nav.services': 'Layanan',
    'nav.services.booking': 'Pemesanan',
    'nav.facilities': 'Sewa Fasilitas',
    'nav.events': 'Event & Kegiatan',
    'nav.info': 'Informasi',
    'nav.info.visit': 'Kunjungan',
    'nav.info.contact': 'Kontak',
    'nav.visitInfo': 'Informasi Kunjungan',
    'nav.faq': 'Tanya Jawab',
    'nav.contact': 'Hubungi Kami',

    // Header
    'header.login': 'Masuk',
    'header.book': 'Pesan Sekarang',
    'header.language': 'Bahasa',
    'header.menuOpen': 'Buka menu',
    'header.menuClose': 'Tutup menu',

    // Footer
    'footer.about':
        'Sistem Informasi Wisata Pasirnanjung. Informasi destinasi, pemesanan fasilitas, dan pendaftaran acara Desa Wisata Pasirnanjung Geulis.',
    'footer.explore': 'Jelajah Desa',
    'footer.services': 'Layanan & Informasi',
    'footer.contact': 'Kontak',
    'footer.phone': 'Telepon',
    'footer.noContact': 'Informasi kontak akan segera tersedia.',
    'footer.rights': 'Seluruh hak cipta dilindungi.',

    // Landing: meta and hero
    'meta.description':
        'Desa Wisata Pasirnanjung Geulis: jelajahi destinasi alam dan budaya, ikuti kegiatan bersama warga, sewa fasilitas desa, dan daftar acara secara online.',
    'hero.badge': 'Wisata Desa Pasirnanjung',
    'hero.description':
        'Menyatu dengan alam, budaya, dan warga desa. Jelajahi destinasi, ikuti kegiatan bersama masyarakat, dan sewa fasilitas desa untuk acaramu.',
    'hero.explore': 'Jelajahi Destinasi',
    'hero.rent': 'Sewa Fasilitas',
    'hero.video': 'Tonton Video',
    'hero.scroll': 'Gulir ke bawah',

    // Landing: about
    'about.eyebrow': 'Tentang Desa',
    'about.title': 'Mengenal Desa Wisata Pasirnanjung Geulis',
    'about.p1':
        'Pasirnanjung Geulis adalah desa wisata yang mengajak pengunjung menikmati alam, budaya, dan keseharian warganya. Di sini kamu bisa menjelajahi destinasi alam, mengikuti kegiatan bersama masyarakat, membawa pulang produk lokal, dan menyewa fasilitas desa untuk acara.',
    'about.p2':
        'Lewat situs ini kamu dapat melihat destinasi dan kegiatan, memesan fasilitas, serta mendaftar acara dengan mudah.',
    'about.point1': 'Pesan tanpa perlu membuat akun',
    'about.point2': 'Dikonfirmasi langsung oleh pengelola desa',
    'about.point3': 'Pembayaran lewat transfer atau QRIS',
    'about.imageAlt': 'Suasana Desa Wisata Pasirnanjung Geulis',

    // Landing: sections
    'destinations.eyebrow': 'Destinasi Wisata',
    'destinations.title': 'Jelajahi Pesona Alam & Warisan Pasirnanjung',
    'destinations.description':
        'Dari pemandangan alam hingga ruang budaya warga, temukan tempat-tempat yang paling berkesan di desa.',
    'activities.eyebrow': 'Pengalaman Wisata',
    'activities.title': 'Temukan Pengalamanmu di Desa',
    'activities.description':
        'Kegiatan seru bersama warga yang bisa kamu ikuti selama berkunjung.',
    'activities.needsReservation': 'Perlu reservasi',
    'activities.noReservation': 'Tanpa reservasi',
    'activities.ask': 'Tanya pengelola',
    'activities.askMessage': 'Halo, saya ingin bertanya tentang {name}.',
    'facilities.eyebrow': 'Layanan Desa',
    'facilities.title': 'Sewa Fasilitas Desa Wisata',
    'facilities.description':
        'Gunakan fasilitas desa untuk pertemuan, acara keluarga, atau kegiatan kelompokmu. Pilih tanggal, kirim pesanan, dan pengelola akan mengonfirmasinya setelah bukti pembayaran diverifikasi.',
    'facilities.from': 'mulai {price} / hari',
    'facilities.cta': 'Pesan Fasilitas',
    'products.eyebrow': 'Karya Warga Desa',
    'products.title': 'Produk Pilihan Desa',
    'products.description':
        'Oleh-oleh dan karya UMKM warga. Hubungi pembuatnya langsung untuk memesan.',
    'products.contact': 'Hubungi via WhatsApp',
    'products.message': 'Halo, saya tertarik dengan {name}.',
    'events.eyebrow': 'Agenda Desa',
    'events.title': 'Event & Kegiatan Mendatang',
    'events.all': 'Lihat semua acara →',
    'events.free': 'Gratis',
    'events.register': 'Daftar',
    'stories.eyebrow': 'Kabar Desa',
    'stories.title': 'Cerita dari Pasirnanjung',
    'stories.description': 'Kabar, budaya, dan kisah warga desa wisata.',
    'stories.culture': 'Budaya',
    'stories.village_potential': 'Potensi Desa',
    'stories.community': 'Masyarakat',
    'visit.eyebrow': 'Informasi Kunjungan',
    'visit.title': 'Informasi Kunjungan & Tanya Jawab',
    'visit.map': 'Buka di Peta',
    'visit.whatsapp': 'Hubungi WhatsApp',
    'cta.title': 'Ayo Berkunjung ke Pasirnanjung',
    'cta.description':
        'Rencanakan kunjunganmu, pesan fasilitas, atau tanyakan langsung kepada pengelola desa.',
    'cta.contact': 'Hubungi Kami',
} as const;

export type MessageKey = keyof typeof id;

const en: Record<MessageKey, string> = {
    'nav.home': 'Home',
    'nav.explore': 'Explore',
    'nav.explore.tourism': 'Tourism',
    'nav.explore.village': 'Village Works',
    'nav.destinations': 'Destinations',
    'nav.activities': 'Experiences',
    'nav.products': 'Village Products',
    'nav.stories': 'Village Stories',
    'nav.services': 'Services',
    'nav.services.booking': 'Booking',
    'nav.facilities': 'Facility Rental',
    'nav.events': 'Events & Activities',
    'nav.info': 'Information',
    'nav.info.visit': 'Visiting',
    'nav.info.contact': 'Contact',
    'nav.visitInfo': 'Visit Information',
    'nav.faq': 'FAQ',
    'nav.contact': 'Contact Us',

    'header.login': 'Log in',
    'header.book': 'Book Now',
    'header.language': 'Language',
    'header.menuOpen': 'Open menu',
    'header.menuClose': 'Close menu',

    'footer.about':
        'Pasirnanjung Tourism Information System. Destination information, facility booking, and event registration for Pasirnanjung Geulis Tourism Village.',
    'footer.explore': 'Explore the Village',
    'footer.services': 'Services & Information',
    'footer.contact': 'Contact',
    'footer.phone': 'Phone',
    'footer.noContact': 'Contact information will be available soon.',
    'footer.rights': 'All rights reserved.',

    'meta.description':
        'Pasirnanjung Geulis Tourism Village: explore natural and cultural destinations, join activities with the villagers, rent village facilities, and register for events online.',
    'hero.badge': 'Pasirnanjung Village Tourism',
    'hero.description':
        'Be one with nature, culture, and the villagers. Explore destinations, join community activities, and rent village facilities for your event.',
    'hero.explore': 'Explore Destinations',
    'hero.rent': 'Rent Facilities',
    'hero.video': 'Watch Video',
    'hero.scroll': 'Scroll down',

    'about.eyebrow': 'About the Village',
    'about.title': 'Get to Know Pasirnanjung Geulis Tourism Village',
    'about.p1':
        'Pasirnanjung Geulis is a tourism village that invites visitors to enjoy its nature, culture, and the daily life of its people. Here you can explore natural destinations, join activities with the community, take home local products, and rent village facilities for events.',
    'about.p2':
        'On this site you can browse destinations and activities, book facilities, and register for events with ease.',
    'about.point1': 'Book without creating an account',
    'about.point2': 'Confirmed directly by the village managers',
    'about.point3': 'Pay by bank transfer or QRIS',
    'about.imageAlt': 'The atmosphere of Pasirnanjung Geulis Tourism Village',

    'destinations.eyebrow': 'Tourist Destinations',
    'destinations.title': 'Discover the Nature & Heritage of Pasirnanjung',
    'destinations.description':
        'From natural scenery to the cultural spaces of the villagers, find the most memorable places in the village.',
    'activities.eyebrow': 'Experiences',
    'activities.title': 'Find Your Experience in the Village',
    'activities.description':
        'Fun activities with the villagers that you can join during your visit.',
    'activities.needsReservation': 'Reservation required',
    'activities.noReservation': 'No reservation needed',
    'activities.ask': 'Ask the manager',
    'activities.askMessage': 'Hello, I would like to ask about {name}.',
    'facilities.eyebrow': 'Village Services',
    'facilities.title': 'Rent Village Facilities',
    'facilities.description':
        'Use village facilities for meetings, family events, or group activities. Pick a date, send your order, and the managers will confirm it once the proof of payment is verified.',
    'facilities.from': 'from {price} / day',
    'facilities.cta': 'Book a Facility',
    'products.eyebrow': 'Made by Villagers',
    'products.title': 'Selected Village Products',
    'products.description':
        'Souvenirs and crafts by village micro-businesses. Contact the maker directly to order.',
    'products.contact': 'Contact via WhatsApp',
    'products.message': 'Hello, I am interested in {name}.',
    'events.eyebrow': 'Village Agenda',
    'events.title': 'Upcoming Events & Activities',
    'events.all': 'See all events →',
    'events.free': 'Free',
    'events.register': 'Register',
    'stories.eyebrow': 'Village News',
    'stories.title': 'Stories from Pasirnanjung',
    'stories.description': 'News, culture, and stories of the tourism village.',
    'stories.culture': 'Culture',
    'stories.village_potential': 'Village Potential',
    'stories.community': 'Community',
    'visit.eyebrow': 'Visit Information',
    'visit.title': 'Visit Information & FAQ',
    'visit.map': 'Open in Maps',
    'visit.whatsapp': 'Contact via WhatsApp',
    'cta.title': 'Come Visit Pasirnanjung',
    'cta.description':
        'Plan your visit, book a facility, or ask the village managers directly.',
    'cta.contact': 'Contact Us',
};

const messages: Record<Locale, Record<MessageKey, string>> = { id, en };

export type Translate = (
    key: MessageKey,
    params?: Record<string, string | number>,
) => string;

export function useTranslation(): { t: Translate; locale: Locale } {
    const { locale } = usePage().props;

    const t: Translate = (key, params) => {
        let text = messages[locale]?.[key] ?? id[key];

        for (const [name, value] of Object.entries(params ?? {})) {
            text = text.replace(`{${name}}`, String(value));
        }

        return text;
    };

    return { t, locale };
}

export const localeNames: Record<Locale, string> = {
    id: 'Bahasa Indonesia',
    en: 'English',
};
