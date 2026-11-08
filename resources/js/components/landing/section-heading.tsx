import { cn } from '@/lib/utils';

export default function SectionHeading({
    eyebrow,
    title,
    description,
    centered = false,
    inverted = false,
}: {
    eyebrow: string;
    title: string;
    description?: string;
    centered?: boolean;
    inverted?: boolean;
}) {
    return (
        <div className={cn('max-w-2xl', centered && 'mx-auto text-center')}>
            <p
                className={cn(
                    'mb-3 text-xs font-semibold tracking-[0.25em] uppercase',
                    inverted ? 'text-clay' : 'text-clay',
                )}
            >
                {eyebrow}
            </p>
            <h2
                className={cn(
                    'font-serif text-3xl leading-tight font-semibold md:text-4xl',
                    inverted ? 'text-cream' : 'text-forest-900',
                )}
            >
                {title}
            </h2>
            {description && (
                <p
                    className={cn(
                        'mt-4 leading-relaxed',
                        inverted ? 'text-forest-100' : 'text-forest-800/80',
                    )}
                >
                    {description}
                </p>
            )}
        </div>
    );
}
