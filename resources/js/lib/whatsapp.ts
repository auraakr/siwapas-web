/**
 * Adds a prefilled message to a wa.me link; returns the link untouched if it cannot be parsed.
 */
export function withWhatsappText(url: string, text: string): string {
    try {
        const parsed = new URL(url);
        parsed.searchParams.set('text', text);

        return parsed.toString();
    } catch {
        return url;
    }
}
