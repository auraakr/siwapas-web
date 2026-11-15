import type { ReactNode } from 'react';

/**
 * Title band at the top of the inner pages: one h1, a short intro, optional extras below.
 */
export default function PageHeader({
    eyebrow,
    title,
    description,
    children,
}: {
    eyebrow: string;
    title: string;
    description?: string;
    children?: ReactNode;
}) {
    return (
        <section className="relative isolate overflow-hidden bg-forest-900 text-cream">
            <div
                aria-hidden="true"
                className="absolute inset-0 -z-10 bg-linear-to-br from-forest-950 via-forest-900 to-forest-700"
            />
            <div className="mx-auto max-w-6xl px-4 py-14 sm:px-6 md:py-20">
                <p className="mb-3 text-xs font-semibold tracking-[0.25em] text-brand-yellow uppercase">
                    {eyebrow}
                </p>
                <h1 className="max-w-3xl font-serif text-4xl leading-tight font-semibold md:text-5xl">
                    {title}
                </h1>
                {description && (
                    <p className="mt-4 max-w-2xl leading-relaxed text-forest-100 md:text-lg">
                        {description}
                    </p>
                )}
                {children}
            </div>
        </section>
    );
}
