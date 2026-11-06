export const orderStatusLabels: Record<string, string> = {
    pending_payment: 'Menunggu pembayaran',
    awaiting_verification: 'Menunggu verifikasi',
    paid: 'Dibayar',
    confirmed: 'Terkonfirmasi',
    rejected: 'Ditolak',
    expired: 'Kedaluwarsa',
    cancelled: 'Dibatalkan',
    refunded: 'Dikembalikan',
};

export const eventStatusLabels: Record<string, string> = {
    draft: 'Draf',
    published: 'Terbit',
    cancelled: 'Dibatalkan',
};
