/**
 * Heading for pages that live inside the padded content column (the full-width band is PageHeader).
 */
export default function PageTitle({
    eyebrow,
    title,
    description,
}: {
    eyebrow: string;
    title: string;
    description?: string;
}) {
    return (
        <header className="mb-8 max-w-2xl md:mb-10">
            <p className="mb-2 text-xs font-semibold tracking-[0.25em] text-clay-dark uppercase">
                {eyebrow}
            </p>
            <h1 className="font-serif text-3xl leading-tight font-semibold text-forest-900 md:text-4xl">
                {title}
            </h1>
            {description && (
                <p className="mt-3 leading-relaxed text-forest-800/80">
                    {description}
                </p>
            )}
        </header>
    );
}
