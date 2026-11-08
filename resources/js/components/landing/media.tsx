import { Mountain } from 'lucide-react';
import { cn } from '@/lib/utils';

/**
 * A photo, or a calm green placeholder while no photo has been uploaded yet.
 */
export default function Media({
    src,
    alt,
    className,
}: {
    src: string | null;
    alt: string;
    className?: string;
}) {
    if (src) {
        return (
            <img
                src={src}
                alt={alt}
                loading="lazy"
                className={cn('size-full object-cover', className)}
            />
        );
    }

    return (
        <div
            role="img"
            aria-label={alt}
            className={cn(
                'flex size-full items-center justify-center bg-linear-to-br from-forest-700 via-forest-600 to-forest-800 text-forest-200/60',
                className,
            )}
        >
            <Mountain className="size-1/4 max-h-16 min-h-8" />
        </div>
    );
}
