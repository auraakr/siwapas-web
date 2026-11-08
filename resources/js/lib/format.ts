const DISPLAY_TIMEZONE = 'Asia/Jakarta';

export function formatRupiah(amount: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
}

export function formatDate(value: string): string {
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'long',
        timeZone: 'UTC',
    }).format(new Date(`${value}T00:00:00Z`));
}

export function dateParts(value: string): { day: string; month: string } {
    const date = new Date(value);

    return {
        day: new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            timeZone: DISPLAY_TIMEZONE,
        }).format(date),
        month: new Intl.DateTimeFormat('id-ID', {
            month: 'short',
            timeZone: DISPLAY_TIMEZONE,
        })
            .format(date)
            .toUpperCase(),
    };
}

export function formatTime(value: string): string {
    return new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        timeZone: DISPLAY_TIMEZONE,
    }).format(new Date(value));
}

export function formatDateTime(value: string): string {
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'long',
        timeStyle: 'short',
        timeZone: DISPLAY_TIMEZONE,
    }).format(new Date(value));
}
