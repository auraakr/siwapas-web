import { usePage } from '@inertiajs/react';

export type Locale = 'id' | 'en';

const id = {
    // Navigation
    'nav.home': 'Beranda',
    'nav.explore': 'Jelajah',
    'nav.explore.tourism': 'Wisata',
    'nav.explore.village': 'Karya Desa',
    'nav.destinations': 'Destinasi',
    'nav.activities': 'Pengalaman Wisata',
    'nav.products': 'Produk UMKM',
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
    'products.contact': 'Pesan via WhatsApp',
    'products.message':
        'Halo, saya ingin memesan {name}. Apakah produk ini masih tersedia?',
    'posters.eyebrow': 'Informasi Terbaru',
    'posters.title': 'Poster & Pengumuman',
    'posters.prev': 'Poster sebelumnya',
    'posters.next': 'Poster berikutnya',
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

    // Navigation (pages)
    'nav.gallery': 'Galeri',
    'nav.about': 'Profil Desa',

    // Gallery
    'gallery.eyebrow': 'Galeri Desa',
    'gallery.title': 'Potret Alam, Budaya, dan Warga',
    'gallery.description':
        'Foto dan video kegiatan, alam, dan keseharian di Desa Wisata Pasirnanjung Geulis.',
    'gallery.all': 'Semua',
    'gallery.filter': 'Saring berdasarkan kategori',
    'gallery.empty': 'Galeri akan segera diisi.',
    'gallery.open': 'Buka {title}',
    'gallery.untitled': 'Foto galeri',
    'gallery.video': 'Video',
    'gallery.close': 'Tutup',
    'gallery.prev': 'Sebelumnya',
    'gallery.next': 'Berikutnya',

    // Destination pages
    'destinations.indexTitle': 'Destinasi & UMKM',
    'destinations.indexDescription':
        'Temukan tempat, kegiatan, dan karya UMKM warga yang bisa kamu nikmati selama berkunjung ke Pasirnanjung Geulis.',
    'destinations.empty': 'Destinasi akan segera tersedia.',
    'destinations.viewAll': 'Lihat semua destinasi →',
    'destinations.back': 'Semua destinasi',
    'destinations.about': 'Tentang tempat ini',
    'destinations.address': 'Alamat',
    'destinations.openingHours': 'Jam buka',
    'destinations.facilities': 'Fasilitas',
    'destinations.rules': 'Tata tertib',
    'destinations.openMap': 'Buka di peta',
    'destinations.watchVideo': 'Tonton video',
    'destinations.related': 'Destinasi lainnya',
    'destinations.photo': 'Foto {name}',
    'destinations.type.destination': 'Destinasi',
    'destinations.type.activity': 'Pengalaman',

    // Village profile
    'village.eyebrow': 'Profil Desa',
    'village.title': 'Mengenal Pasirnanjung Geulis Lebih Dekat',
    'village.description':
        'Cerita, nilai, dan orang-orang di balik desa wisata kami.',
    'village.story': 'Cerita Desa',
    'village.vision': 'Visi & Misi',
    'village.team': 'Pengelola & Tim',
    'village.teamDescription':
        'Warga yang mengelola desa wisata dan menyambut kedatanganmu.',

    // Contact page
    'contact.eyebrow': 'Hubungi Kami',
    'contact.title': 'Ada Pertanyaan? Tulis kepada Kami',
    'contact.description':
        'Tanyakan soal kunjungan, rombongan, atau kerja sama. Pengelola akan membalas secepatnya.',
    'contact.name': 'Nama',
    'contact.phone': 'Nomor WhatsApp',
    'contact.email': 'Email',
    'contact.subject': 'Subjek',
    'contact.message': 'Pesan',
    'contact.optional': '(opsional)',
    'contact.hint': 'Isi nomor WhatsApp atau email agar kami bisa membalas.',
    'contact.send': 'Kirim Pesan',
    'contact.sending': 'Mengirim…',
    'contact.sentTitle': 'Pesan terkirim',
    'contact.sentDescription':
        'Terima kasih! Pengelola desa akan menghubungimu secepatnya.',
    'contact.sendAnother': 'Kirim pesan lain',
    'contact.home': 'Kembali ke beranda',
    'contact.direct': 'Hubungi langsung',

    'facilities.capacity': 'Kapasitas {count} orang',
    'facilities.priceFrom': 'Mulai dari',
    'facilities.perDay': '/ hari',
    'facilities.step1': 'Pilih fasilitas & tanggal',
    'facilities.step2': 'Transfer atau bayar via QRIS',
    'facilities.step3': 'Dikonfirmasi pengelola',
    'facilities.all': 'Lihat Semua Fasilitas',
    'facilities.none': 'Belum ada fasilitas yang tersedia.',
    'visit.moreQuestions': 'Belum menemukan jawabannya?',
    'booking.priceNote':
        'Harga dasar per hari. Total akhir dihitung otomatis saat kamu memesan.',
    'booking.formTitle': 'Reservasi',
    'booking.unavailableTitle': 'Tanggal yang sudah terisi',
    'booking.unavailableNote':
        'Tanggal awal adalah check-in dan tanggal akhir adalah check-out. Kamu bisa check-in di hari check-out tamu lain.',
    'booking.checkIn': 'Check-in',
    'booking.checkOut': 'Check-out',
    'booking.guests': 'Jumlah tamu',
    'booking.name': 'Nama',
    'booking.phone': 'Nomor telepon',
    'booking.phoneHint': 'Format 08xxxxxxxxxx, tanpa spasi atau tanda hubung.',
    'booking.email': 'Email (opsional)',
    'booking.notes': 'Catatan (opsional)',
    'booking.submit': 'Pesan Sekarang',
    'booking.afterSubmit':
        'Tanpa perlu akun. Booking terkonfirmasi setelah bukti transfer DP diverifikasi pengelola.',
    'booking.rules': 'Tata tertib',
    'events.pageDescription':
        'Kegiatan budaya, alam, dan komunitas di Pasirnanjung Geulis. Daftar langsung secara online.',
    'events.none': 'Belum ada acara yang akan datang.',
    'events.perPerson': '{price} / peserta',
    'events.quotaLeft': 'Sisa kuota: {count}',
    'events.full': 'Kuota acara ini sudah penuh.',
    'events.requirements': 'Persyaratan',
    'events.organizer': 'Penyelenggara: {name}',
    'events.formTitle': 'Daftar',
    'events.responsible': 'Nama penanggung jawab',
    'events.participant': 'Peserta {number}',
    'events.addParticipant': 'Tambah peserta',
    'events.removeParticipant': 'Hapus peserta terakhir',
    'events.participantMax': 'Maksimal {count} peserta per pendaftaran.',
    'events.subtotal': 'Perkiraan subtotal: {amount}',
    'events.success': 'Pendaftaran berhasil. Kode pendaftaran kamu:',
    'order.title': 'Pesanan',
    'order.saveLinkTitle': 'Simpan tautan pesanan',
    'order.saveLinkText':
        'Kami tidak mengirim email. Simpan tautan halaman ini untuk memantau status pesananmu.',
    'order.copyLink': 'Salin tautan',
    'order.shareWhatsapp': 'Kirim ke WhatsApp',
    'order.shareText': 'Tautan pesanan {code}:',
    'order.status.pending_payment': 'Menunggu pembayaran',
    'order.status.awaiting_verification': 'Menunggu verifikasi',
    'order.status.paid': 'Dibayar',
    'order.status.confirmed': 'Terkonfirmasi',
    'order.status.rejected': 'Ditolak',
    'order.status.expired': 'Kedaluwarsa',
    'order.status.cancelled': 'Dibatalkan',
    'order.status.refunded': 'Dikembalikan',
    'order.step.pay': 'Bayar',
    'order.step.verify': 'Verifikasi',
    'order.step.done': 'Selesai',
    'order.confirmedNote':
        'Pesananmu sudah terkonfirmasi. Sampai jumpa di Pasirnanjung Geulis!',
    'order.ended.expired':
        'Pesanan ini sudah kedaluwarsa. Silakan buat pesanan baru.',
    'order.ended.cancelled': 'Pesanan ini telah dibatalkan.',
    'order.ended.refunded': 'Pembayaran untuk pesanan ini telah dikembalikan.',
    'order.rejected': 'Pembayaran ditolak: {reason}',
    'order.guests': '{count} tamu',
    'order.subtotal': 'Subtotal',
    'order.uniqueCode': 'Kode unik',
    'order.total': 'Total transfer',
    'order.exactAmount':
        'Transfer sesuai nominal tepat agar mudah diverifikasi.',
    'order.payVia': 'Bayar melalui',
    'order.payBefore': 'Bayar sebelum {time}',
    'order.accountHolder': 'a.n. {name}',
    'order.uploadTitle': 'Unggah bukti transfer',
    'order.proof': 'Bukti transfer (JPG, PNG, PDF, maks. 5 MB)',
    'order.sender': 'Nama pengirim (opsional)',
    'order.sendProof': 'Kirim Bukti',
    'order.verifying':
        'Bukti transfer diterima. Verifikasi paling lambat {hours} jam.',
    'copy.copy': 'Salin',
    'copy.done': 'Tersalin',
    'copy.failed': 'Gagal menyalin',

    'nav.profile': 'Profil Wisata',
    'nav.visit': 'Kunjungan Kami',
    'village.sections': 'Bagian halaman',
    'village.location': 'Lokasi',
    'visit.pageTitle': 'Rencanakan Kunjunganmu',
    'visit.pageDescription':
        'Jam buka, cara reservasi, dan ketentuan sebelum kamu datang ke Pasirnanjung Geulis.',
    'visit.hoursLabel': 'Jam operasional',
    'visit.steps': 'Tata Cara Reservasi',
    'visit.step1.title': 'Pilih fasilitas',
    'visit.step1.text': 'Tentukan fasilitas yang ingin kamu gunakan.',
    'visit.step2.title': 'Cek jadwal & isi formulir',
    'visit.step2.text':
        'Lihat tanggal yang masih tersedia di halaman fasilitas, lalu isi formulir pemesanan.',
    'visit.step3.title': 'Bayar & unggah bukti',
    'visit.step3.text':
        'Transfer atau bayar lewat QRIS sesuai nominal, lalu unggah bukti pembayaran.',
    'visit.step4.title': 'Dikonfirmasi pengelola',
    'visit.step4.text':
        'BUMDes memeriksa pembayaranmu dan mengonfirmasi pesanan.',
    'visit.seeFacilities': 'Lihat Fasilitas & Harga',
    'visit.needHelp': 'Masih ada yang ingin ditanyakan?',
    'visit.askMessage':
        'Halo, saya ingin bertanya tentang kunjungan dan reservasi di Desa Wisata Pasirnanjung Geulis.',
    // Destinations & UMKM page
    'umkm.tabDestinations': 'Destinasi',
    'umkm.tabProducts': 'Produk UMKM',
    'umkm.eyebrow': 'Produk UMKM',
    'umkm.title': 'Karya dari Tangan Warga',
    'umkm.description':
        'Aneka produk dan kuliner UMKM dibuat oleh warga desa sendiri. Setiap pembelian ikut mendukung usaha masyarakat.',
    'umkm.viewAll': 'Lihat Semua Produk',
    'umkm.details': 'Lihat detail {name}',
    'umkm.maker': 'Dibuat oleh',
    'umkm.photo': 'Foto {number} {name}',
    'umkm.noDescription':
        'Hubungi pembuatnya untuk informasi lengkap produk ini.',
    'umkm.emptyTitle': 'Produk UMKM segera hadir',
    'umkm.emptyText':
        'Pengelola sedang menyiapkan daftar produk warga. Kunjungi lagi halaman ini dalam waktu dekat.',

    // Down payment (DP) for facility reservations
    'booking.dpIntro':
        'Cukup bayar DP {percent}% dari harga lunas untuk mengamankan tanggalmu. Sisanya dibayar sebelum atau saat kedatangan. Pilih tanggal untuk melihat rinciannya.',
    'booking.totalFor': 'Harga lunas ({nights} malam)',
    'booking.dpNow': 'DP {percent}% dibayar sekarang',
    'booking.remainingLater': 'Sisa pelunasan (nanti)',
    'booking.uniqueCodeNote':
        'Kode unik 1–999 rupiah ditambahkan ke transfer DP agar pembayaranmu mudah dikenali.',
    'booking.tooSoon':
        'Tanggal check-in ini terlalu dekat untuk membayar DP secara online. Pilih tanggal lain atau hubungi pengelola.',
    'booking.datesTaken':
        'Tanggal ini sudah terisi. Silakan pilih tanggal lain.',
    'booking.askManager': 'Hubungi pengelola via WhatsApp',
    'booking.askMessage':
        'Halo, saya ingin memesan {name} untuk tanggal dekat. Apakah masih bisa?',
    'order.status.dp_paid': 'DP lunas, booking terkonfirmasi',
    'order.status.awaiting_settlement_verification':
        'Menunggu verifikasi pelunasan',
    'order.status.paid_in_full': 'Lunas',
    'order.step.payDp': 'Bayar DP',
    'order.step.verifyDp': 'DP diverifikasi',
    'order.step.settle': 'Pelunasan',
    'order.step.paidInFull': 'Lunas',
    'order.ended.rejected':
        'Pesanan ini ditolak. Hubungi pengelola jika menurutmu ada kekeliruan.',
    'order.rejectedSettlement':
        'Bukti pelunasan ditolak: {reason}. Booking-mu tetap berlaku, silakan unggah bukti yang benar.',
    'order.uploadAgainBefore':
        'Silakan unggah bukti yang benar sebelum {time}.',
    'order.verifyingDp':
        'Bukti transfer DP diterima. Verifikasi paling lambat {hours} jam. Setelah itu booking-mu terkonfirmasi.',
    'order.dpPaidNote':
        'DP sudah kami terima, booking-mu terkonfirmasi! Sisa {amount} bisa ditransfer lalu unggah buktinya di bawah, atau dibayar tunai saat kedatangan.',
    'order.verifyingSettlement':
        'Bukti pelunasan diterima. Verifikasi paling lambat {hours} jam.',
    'order.paidInFullNote':
        'Pembayaran sudah lunas. Terima kasih, sampai jumpa di Pasirnanjung Geulis!',
    'order.paidInFullCash':
        'Pembayaran sudah lunas (tunai di lokasi). Terima kasih, sampai jumpa lagi di Pasirnanjung Geulis!',
    'order.settlementClosed':
        'Batas unggah bukti pelunasan sudah lewat (setelah tanggal check-out). Sisa {amount} belum tercatat lunas, silakan hubungi pengelola.',
    'order.contactManagers': 'Hubungi pengelola',
    'order.askManagerMessage':
        'Halo, saya ingin menyelesaikan pembayaran pesanan {code}.',
    'order.dpForfeited':
        'Sesuai ketentuan, DP tidak dapat dikembalikan untuk pembatalan oleh penyewa.',
    'order.fullPrice': 'Harga lunas',
    'order.dpPart': 'DP {percent}%',
    'order.uniqueCodeOnDp': 'Kode unik (ditambahkan ke DP)',
    'order.remaining': 'Sisa pelunasan',
    'order.transferDp': 'Transfer DP sekarang',
    'order.transferSettlement': 'Transfer pelunasan',
    'order.settlementExact':
        'Transfer pelunasan sesuai nominal di atas, tanpa kode unik.',
    'order.payDpBefore': 'Bayar DP sebelum {time}',
    'order.settleOptions':
        'Transfer sisa pembayaran lalu unggah buktinya paling lambat {date} (tanggal check-out), atau bayar tunai di lokasi.',
    'order.uploadDpTitle': 'Unggah bukti transfer DP',
    'order.uploadSettlementTitle': 'Unggah bukti pelunasan',
} as const;

export type MessageKey = keyof typeof id;

const en: Record<MessageKey, string> = {
    'nav.home': 'Home',
    'nav.explore': 'Explore',
    'nav.explore.tourism': 'Tourism',
    'nav.explore.village': 'Village Works',
    'nav.destinations': 'Destinations',
    'nav.activities': 'Experiences',
    'nav.products': 'Local Products',
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
    'products.contact': 'Order via WhatsApp',
    'products.message':
        'Hello, I would like to order {name}. Is it still available?',
    'posters.eyebrow': 'Latest Updates',
    'posters.title': 'Posters & Announcements',
    'posters.prev': 'Previous poster',
    'posters.next': 'Next poster',
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

    'nav.gallery': 'Gallery',
    'nav.about': 'Village Profile',

    'gallery.eyebrow': 'Village Gallery',
    'gallery.title': 'Portraits of Nature, Culture, and People',
    'gallery.description':
        'Photos and videos of activities, nature, and daily life in Pasirnanjung Geulis Tourism Village.',
    'gallery.all': 'All',
    'gallery.filter': 'Filter by category',
    'gallery.empty': 'The gallery will be filled soon.',
    'gallery.open': 'Open {title}',
    'gallery.untitled': 'Gallery photo',
    'gallery.video': 'Video',
    'gallery.close': 'Close',
    'gallery.prev': 'Previous',
    'gallery.next': 'Next',

    'destinations.indexTitle': 'Destinations & Local Products',
    'destinations.indexDescription':
        'Discover places, experiences, and products made by local businesses to enjoy during your visit to Pasirnanjung Geulis.',
    'destinations.empty': 'Destinations will be available soon.',
    'destinations.viewAll': 'See all destinations →',
    'destinations.back': 'All destinations',
    'destinations.about': 'About this place',
    'destinations.address': 'Address',
    'destinations.openingHours': 'Opening hours',
    'destinations.facilities': 'Facilities',
    'destinations.rules': 'House rules',
    'destinations.openMap': 'Open in Maps',
    'destinations.watchVideo': 'Watch video',
    'destinations.related': 'More to explore',
    'destinations.photo': 'Photo of {name}',
    'destinations.type.destination': 'Destination',
    'destinations.type.activity': 'Experience',

    'village.eyebrow': 'Village Profile',
    'village.title': 'Get to Know Pasirnanjung Geulis Better',
    'village.description':
        'The story, values, and people behind our tourism village.',
    'village.story': 'Our Story',
    'village.vision': 'Vision & Mission',
    'village.team': 'Managers & Team',
    'village.teamDescription':
        'The villagers who run the tourism village and welcome your visit.',

    'contact.eyebrow': 'Contact Us',
    'contact.title': 'Have a Question? Write to Us',
    'contact.description':
        'Ask about visits, group trips, or partnerships. The managers will reply as soon as possible.',
    'contact.name': 'Name',
    'contact.phone': 'WhatsApp number',
    'contact.email': 'Email',
    'contact.subject': 'Subject',
    'contact.message': 'Message',
    'contact.optional': '(optional)',
    'contact.hint': 'Enter a WhatsApp number or email so we can reply.',
    'contact.send': 'Send Message',
    'contact.sending': 'Sending…',
    'contact.sentTitle': 'Message sent',
    'contact.sentDescription':
        'Thank you! The village managers will get in touch soon.',
    'contact.sendAnother': 'Send another message',
    'contact.home': 'Back to home',
    'contact.direct': 'Reach us directly',

    'facilities.capacity': 'Capacity {count} people',
    'facilities.priceFrom': 'From',
    'facilities.perDay': '/ day',
    'facilities.step1': 'Choose a facility & dates',
    'facilities.step2': 'Pay by transfer or QRIS',
    'facilities.step3': 'Confirmed by the managers',
    'facilities.all': 'See All Facilities',
    'facilities.none': 'No facilities are available yet.',
    'visit.moreQuestions': "Didn't find your answer?",
    'booking.priceNote':
        'Base price per day. The final total is calculated automatically when you book.',
    'booking.formTitle': 'Reservation',
    'booking.unavailableTitle': 'Dates already booked',
    'booking.unavailableNote':
        'The first date is check-in and the last is check-out. You can check in on another guest’s check-out day.',
    'booking.checkIn': 'Check-in',
    'booking.checkOut': 'Check-out',
    'booking.guests': 'Number of guests',
    'booking.name': 'Name',
    'booking.phone': 'Phone number',
    'booking.phoneHint': 'Format 08xxxxxxxxxx, without spaces or dashes.',
    'booking.email': 'Email (optional)',
    'booking.notes': 'Notes (optional)',
    'booking.submit': 'Book Now',
    'booking.afterSubmit':
        'No account needed. Your booking is confirmed once the managers verify the down payment transfer.',
    'booking.rules': 'House rules',
    'events.pageDescription':
        'Cultural, nature, and community activities in Pasirnanjung Geulis. Register online.',
    'events.none': 'There are no upcoming events yet.',
    'events.perPerson': '{price} / person',
    'events.quotaLeft': 'Spots left: {count}',
    'events.full': 'This event is fully booked.',
    'events.requirements': 'Requirements',
    'events.organizer': 'Organizer: {name}',
    'events.formTitle': 'Register',
    'events.responsible': 'Name of the person in charge',
    'events.participant': 'Participant {number}',
    'events.addParticipant': 'Add participant',
    'events.removeParticipant': 'Remove last participant',
    'events.participantMax': 'Up to {count} participants per registration.',
    'events.subtotal': 'Estimated subtotal: {amount}',
    'events.success': 'Registration successful. Your registration code:',
    'order.title': 'Order',
    'order.saveLinkTitle': 'Save your order link',
    'order.saveLinkText':
        'We do not send emails. Save this page’s link to follow your order status.',
    'order.copyLink': 'Copy link',
    'order.shareWhatsapp': 'Send to WhatsApp',
    'order.shareText': 'Order link {code}:',
    'order.status.pending_payment': 'Awaiting payment',
    'order.status.awaiting_verification': 'Awaiting verification',
    'order.status.paid': 'Paid',
    'order.status.confirmed': 'Confirmed',
    'order.status.rejected': 'Rejected',
    'order.status.expired': 'Expired',
    'order.status.cancelled': 'Cancelled',
    'order.status.refunded': 'Refunded',
    'order.step.pay': 'Pay',
    'order.step.verify': 'Verify',
    'order.step.done': 'Done',
    'order.confirmedNote':
        'Your order is confirmed. See you in Pasirnanjung Geulis!',
    'order.ended.expired': 'This order has expired. Please place a new order.',
    'order.ended.cancelled': 'This order has been cancelled.',
    'order.ended.refunded': 'The payment for this order has been refunded.',
    'order.rejected': 'Payment rejected: {reason}',
    'order.guests': '{count} guests',
    'order.subtotal': 'Subtotal',
    'order.uniqueCode': 'Unique code',
    'order.total': 'Total to transfer',
    'order.exactAmount': 'Transfer the exact amount so it is easy to verify.',
    'order.payVia': 'Pay via',
    'order.payBefore': 'Pay before {time}',
    'order.accountHolder': 'Account name: {name}',
    'order.uploadTitle': 'Upload proof of transfer',
    'order.proof': 'Proof of transfer (JPG, PNG, PDF, max. 5 MB)',
    'order.sender': 'Sender name (optional)',
    'order.sendProof': 'Send Proof',
    'order.verifying':
        'Proof of transfer received. Verification takes at most {hours} hours.',
    'copy.copy': 'Copy',
    'copy.done': 'Copied',
    'copy.failed': 'Could not copy',

    'nav.profile': 'Tourism Profile',
    'nav.visit': 'Visit Us',
    'village.sections': 'Sections on this page',
    'village.location': 'Location',
    'visit.pageTitle': 'Plan Your Visit',
    'visit.pageDescription':
        'Opening hours, how to book, and the terms before you come to Pasirnanjung Geulis.',
    'visit.hoursLabel': 'Opening hours',
    'visit.steps': 'How to Book',
    'visit.step1.title': 'Choose a facility',
    'visit.step1.text': 'Decide which facility you would like to use.',
    'visit.step2.title': 'Check dates & fill in the form',
    'visit.step2.text':
        'See the available dates on the facility page, then fill in the booking form.',
    'visit.step3.title': 'Pay & upload proof',
    'visit.step3.text':
        'Pay by transfer or QRIS for the exact amount, then upload your proof of payment.',
    'visit.step4.title': 'Confirmed by the managers',
    'visit.step4.text':
        'The village enterprise (BUMDes) checks your payment and confirms the booking.',
    'visit.seeFacilities': 'See Facilities & Prices',
    'visit.needHelp': 'Still have a question?',
    'visit.askMessage':
        'Hello, I would like to ask about visiting and booking at Pasirnanjung Geulis Tourism Village.',
    'umkm.tabDestinations': 'Destinations',
    'umkm.tabProducts': 'Local Products',
    'umkm.eyebrow': 'Local Products',
    'umkm.title': "Made by Villagers' Hands",
    'umkm.description':
        'Local food and crafts made by the villagers themselves. Every purchase helps support community businesses.',
    'umkm.viewAll': 'See All Products',
    'umkm.details': 'View details of {name}',
    'umkm.maker': 'Made by',
    'umkm.photo': 'Photo {number} of {name}',
    'umkm.noDescription':
        'Contact the maker for full details about this product.',
    'umkm.emptyTitle': 'Local products coming soon',
    'umkm.emptyText':
        'The managers are preparing the list of village products. Please check back soon.',

    'booking.dpIntro':
        'Pay a {percent}% down payment of the full price to secure your dates. The rest is paid before or on arrival. Pick your dates to see the details.',
    'booking.totalFor': 'Full price ({nights} nights)',
    'booking.dpNow': '{percent}% down payment due now',
    'booking.remainingLater': 'Remaining balance (later)',
    'booking.uniqueCodeNote':
        'A unique code of 1–999 rupiah is added to the down payment transfer so we can recognise your payment.',
    'booking.tooSoon':
        'This check-in date is too close to pay the down payment online. Choose another date or contact the managers.',
    'booking.datesTaken':
        'These dates are already booked. Please choose other dates.',
    'booking.askManager': 'Contact the managers via WhatsApp',
    'booking.askMessage':
        'Hello, I would like to book {name} for a date soon. Is that still possible?',
    'order.status.dp_paid': 'Down payment received, booking confirmed',
    'order.status.awaiting_settlement_verification':
        'Awaiting balance verification',
    'order.status.paid_in_full': 'Paid in full',
    'order.step.payDp': 'Pay deposit',
    'order.step.verifyDp': 'Deposit verified',
    'order.step.settle': 'Balance',
    'order.step.paidInFull': 'Paid in full',
    'order.ended.rejected':
        'This order was rejected. Please contact the managers if you think this is a mistake.',
    'order.rejectedSettlement':
        'Balance proof rejected: {reason}. Your booking still stands; please upload the correct proof.',
    'order.uploadAgainBefore': 'Please upload the correct proof before {time}.',
    'order.verifyingDp':
        'Down payment proof received. Verification takes at most {hours} hours, then your booking is confirmed.',
    'order.dpPaidNote':
        'We received your down payment and your booking is confirmed! The remaining {amount} can be transferred with the proof uploaded below, or paid in cash on arrival.',
    'order.verifyingSettlement':
        'Balance proof received. Verification takes at most {hours} hours.',
    'order.paidInFullNote':
        'Your booking is paid in full. Thank you, see you in Pasirnanjung Geulis!',
    'order.paidInFullCash':
        'Your booking is paid in full (cash on site). Thank you, see you again in Pasirnanjung Geulis!',
    'order.settlementClosed':
        'The deadline to upload the balance proof has passed (after the check-out date). The remaining {amount} is not recorded as paid yet; please contact the managers.',
    'order.contactManagers': 'Contact the managers',
    'order.askManagerMessage':
        'Hello, I would like to settle the payment for order {code}.',
    'order.dpForfeited':
        'As stated in the terms, the down payment is not refunded when the tenant cancels.',
    'order.fullPrice': 'Full price',
    'order.dpPart': '{percent}% down payment',
    'order.uniqueCodeOnDp': 'Unique code (added to the down payment)',
    'order.remaining': 'Remaining balance',
    'order.transferDp': 'Transfer the down payment now',
    'order.transferSettlement': 'Transfer the balance',
    'order.settlementExact':
        'Transfer the exact balance above, without a unique code.',
    'order.payDpBefore': 'Pay the down payment before {time}',
    'order.settleOptions':
        'Transfer the remaining balance and upload the proof by {date} (check-out date), or pay in cash on site.',
    'order.uploadDpTitle': 'Upload the down payment proof',
    'order.uploadSettlementTitle': 'Upload the balance proof',
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
