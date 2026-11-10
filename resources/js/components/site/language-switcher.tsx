import { router } from '@inertiajs/react';
import { Check, ChevronDown, Languages } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { localeNames, useTranslation, type Locale } from '@/lib/i18n';
import { update } from '@/routes/locale';

const locales: Locale[] = ['id', 'en'];

export function changeLocale(locale: Locale): void {
    router.post(update().url, { locale }, { preserveScroll: true });
}

export default function LanguageSwitcher() {
    const { t, locale } = useTranslation();

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <button
                    type="button"
                    aria-label={t('header.language')}
                    data-test="language-switcher"
                    className="flex items-center gap-2 rounded-full border border-forest-200 bg-white/70 px-3.5 py-2 text-xs font-semibold tracking-[0.15em] text-forest-900 uppercase transition hover:border-clay hover:text-clay"
                >
                    <Languages className="size-4" />
                    {locale}
                    <ChevronDown className="size-3.5 opacity-70" />
                </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="min-w-44">
                {locales.map((code) => (
                    <DropdownMenuItem
                        key={code}
                        onSelect={() => changeLocale(code)}
                        className="cursor-pointer justify-between"
                    >
                        {localeNames[code]}
                        {code === locale && (
                            <Check className="size-4 text-clay" />
                        )}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
