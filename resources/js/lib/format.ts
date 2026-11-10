const DISPLAY_TIMEZONE = 'Asia/Jakarta';

function intlLocale(locale: string): string {
    return locale === 'en' ? 'en-GB' : 'id-ID';
}

export function formatRupiah(amount: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
}

export function formatDate(value: string, locale: string = 'id'): string {
    return new Intl.DateTimeFormat(intlLocale(locale), {
        dateStyle: 'long',
        timeZone: 'UTC',
    }).format(new Date(`${value}T00:00:00Z`));
}

export function dateParts(
    value: string,
    locale: string = 'id',
): { day: string; month: string } {
    const date = new Date(value);

    return {
        day: new Intl.DateTimeFormat(intlLocale(locale), {
            day: '2-digit',
            timeZone: DISPLAY_TIMEZONE,
        }).format(date),
        month: new Intl.DateTimeFormat(intlLocale(locale), {
            month: 'short',
            timeZone: DISPLAY_TIMEZONE,
        })
            .format(date)
            .toUpperCase(),
    };
}

export function formatTime(value: string, locale: string = 'id'): string {
    return new Intl.DateTimeFormat(intlLocale(locale), {
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
