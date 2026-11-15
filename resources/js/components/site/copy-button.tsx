import { Check, Copy } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';

/**
 * Copies a value (or whatever a function returns at click time) and says so for a moment.
 */
export default function CopyButton({
    value,
    label,
    className,
}: {
    value: string | (() => string);
    label?: string;
    className?: string;
}) {
    const { t } = useTranslation();
    const [state, setState] = useState<'idle' | 'done' | 'failed'>('idle');

    const copy = async () => {
        try {
            await navigator.clipboard.writeText(
                typeof value === 'function' ? value() : value,
            );
            setState('done');
        } catch {
            setState('failed');
        }

        setTimeout(() => setState('idle'), 2000);
    };

    const text =
        state === 'done'
            ? t('copy.done')
            : state === 'failed'
              ? t('copy.failed')
              : (label ?? t('copy.copy'));

    return (
        <button
            type="button"
            onClick={copy}
            className={cn(
                'inline-flex h-10 items-center justify-center gap-2 rounded-md border border-forest-200 bg-white px-3 text-sm font-medium text-forest-900 transition hover:bg-forest-50 focus-visible:ring-2 focus-visible:ring-forest-600 focus-visible:outline-none',
                className,
            )}
        >
            {state === 'done' ? (
                <Check className="size-4 text-forest-600" aria-hidden="true" />
            ) : (
                <Copy className="size-4" aria-hidden="true" />
            )}
            <span role="status">{text}</span>
        </button>
    );
}
