import { Form, Head, Link, usePage } from '@inertiajs/react';
import {
    CheckCircle2,
    Clock,
    ExternalLink,
    Mail,
    MapPin,
    MessageCircle,
    Phone,
} from 'lucide-react';
import PageHeader from '@/components/site/page-header';
import { useTranslation } from '@/lib/i18n';
import { home } from '@/routes';
import contactRoutes from '@/routes/contact';

const fieldClass =
    'w-full rounded-lg border border-forest-200 bg-white px-4 py-2.5 text-forest-950 placeholder:text-forest-700/50 focus:border-forest-600 focus:ring-2 focus:ring-forest-600/30 focus:outline-none aria-invalid:border-red-700';

const linkButton =
    'inline-flex items-center justify-center rounded-md px-6 py-3 text-xs font-semibold tracking-wider uppercase transition focus-visible:ring-2 focus-visible:ring-forest-600 focus-visible:ring-offset-2 focus-visible:outline-none';

type FieldProps = {
    name: string;
    label: string;
    error?: string;
    hint?: string;
    multiline?: boolean;
    type?: string;
    required?: boolean;
    autoComplete?: string;
    inputMode?: 'text' | 'tel' | 'email';
    maxLength?: number;
};

function Field({
    name,
    label,
    error,
    hint,
    multiline = false,
    type = 'text',
    required = false,
    ...rest
}: FieldProps) {
    const id = `contact-${name}`;
    const describedBy =
        [error && `${id}-error`, hint && `${id}-hint`]
            .filter(Boolean)
            .join(' ') || undefined;
    const common = {
        id,
        name,
        required,
        className: fieldClass,
        'aria-invalid': error ? true : undefined,
        'aria-describedby': describedBy,
        ...rest,
    };

    return (
        <div>
            <label
                htmlFor={id}
                className="mb-1.5 block text-sm font-semibold text-forest-900"
            >
                {label}
            </label>
            {multiline ? (
                <textarea rows={6} {...common} />
            ) : (
                <input type={type} {...common} />
            )}
            {hint && !error && (
                <p id={`${id}-hint`} className="mt-1.5 text-xs text-forest-700">
                    {hint}
                </p>
            )}
            {error && (
                <p id={`${id}-error`} className="mt-1.5 text-sm text-red-700">
                    {error}
                </p>
            )}
        </div>
    );
}

export default function Contact({ sent }: { sent: boolean }) {
    const { site } = usePage().props;
    const { t } = useTranslation();
    const info = site.contact;
    const optional = ` ${t('contact.optional')}`;

    const details = [
        info.address && {
            icon: MapPin,
            text: info.address,
            href: info.maps_url,
        },
        info.hours && { icon: Clock, text: info.hours },
        info.phone && {
            icon: Phone,
            text: info.phone,
            href: `tel:${info.phone.replace(/[^\d+]/g, '')}`,
        },
        info.whatsapp_url && {
            icon: MessageCircle,
            text: `WhatsApp ${info.whatsapp ?? ''}`.trim(),
            href: info.whatsapp_url,
        },
        info.email && {
            icon: Mail,
            text: info.email,
            href: `mailto:${info.email}`,
        },
    ].filter(Boolean) as {
        icon: typeof MapPin;
        text: string;
        href?: string;
    }[];

    return (
        <>
            <Head title={t('contact.eyebrow')}>
                <meta name="description" content={t('contact.description')} />
            </Head>

            <PageHeader
                eyebrow={t('contact.eyebrow')}
                title={t('contact.title')}
                description={t('contact.description')}
            />

            <section className="bg-cream py-12 md:py-16">
                <div className="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-[1.4fr_1fr]">
                    {sent ? (
                        <div
                            role="status"
                            className="h-fit rounded-2xl border border-forest-100 bg-white p-8 text-center shadow-sm"
                        >
                            <CheckCircle2
                                className="mx-auto size-12 text-forest-600"
                                aria-hidden="true"
                            />
                            <h2 className="mt-4 font-serif text-2xl font-semibold text-forest-900">
                                {t('contact.sentTitle')}
                            </h2>
                            <p className="mt-2 text-forest-800/80">
                                {t('contact.sentDescription')}
                            </p>
                            <div className="mt-6 flex flex-wrap justify-center gap-3">
                                <Link
                                    href={home()}
                                    className={`${linkButton} bg-clay-dark text-white hover:bg-clay`}
                                >
                                    {t('contact.home')}
                                </Link>
                                <Link
                                    href={contactRoutes.show()}
                                    className={`${linkButton} border border-forest-200 text-forest-900 hover:bg-forest-50`}
                                >
                                    {t('contact.sendAnother')}
                                </Link>
                            </div>
                        </div>
                    ) : (
                        <Form
                            {...contactRoutes.store.form()}
                            disableWhileProcessing
                            className="relative space-y-5 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm md:p-8"
                        >
                            {({ errors, processing }) => (
                                <>
                                    {/* Bot trap: invisible to people, tempting to scripts. */}
                                    <div
                                        aria-hidden="true"
                                        className="absolute -left-[9999px] h-0 w-0 overflow-hidden"
                                    >
                                        <label>
                                            Website
                                            <input
                                                type="text"
                                                name="website"
                                                tabIndex={-1}
                                                autoComplete="off"
                                            />
                                        </label>
                                    </div>

                                    <Field
                                        name="name"
                                        label={t('contact.name')}
                                        error={errors.name}
                                        required
                                        autoComplete="name"
                                        maxLength={255}
                                    />
                                    <div className="grid gap-5 sm:grid-cols-2">
                                        <Field
                                            name="phone"
                                            label={t('contact.phone')}
                                            error={errors.phone}
                                            type="tel"
                                            inputMode="tel"
                                            autoComplete="tel"
                                            maxLength={30}
                                        />
                                        <Field
                                            name="email"
                                            label={t('contact.email')}
                                            error={errors.email}
                                            type="email"
                                            inputMode="email"
                                            autoComplete="email"
                                            maxLength={255}
                                        />
                                    </div>
                                    <p className="-mt-2 text-xs text-forest-700">
                                        {t('contact.hint')}
                                    </p>
                                    <Field
                                        name="subject"
                                        label={`${t('contact.subject')}${optional}`}
                                        error={errors.subject}
                                        maxLength={255}
                                    />
                                    <Field
                                        name="message"
                                        label={t('contact.message')}
                                        error={errors.message}
                                        multiline
                                        required
                                        maxLength={2000}
                                    />
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className={`${linkButton} bg-clay-dark text-white hover:bg-clay disabled:opacity-60`}
                                    >
                                        {processing
                                            ? t('contact.sending')
                                            : t('contact.send')}
                                    </button>
                                </>
                            )}
                        </Form>
                    )}

                    {details.length > 0 && (
                        <aside className="h-fit rounded-2xl bg-forest-900 p-6 text-cream md:p-8">
                            <h2 className="mb-5 font-serif text-xl font-semibold">
                                {t('contact.direct')}
                            </h2>
                            <ul className="space-y-4 text-sm">
                                {details.map(({ icon: Icon, text, href }) => (
                                    <li key={text} className="flex gap-3">
                                        <Icon
                                            className="mt-0.5 size-4 shrink-0 text-brand-yellow"
                                            aria-hidden="true"
                                        />
                                        {href ? (
                                            <a
                                                href={href}
                                                target={
                                                    href.startsWith('http')
                                                        ? '_blank'
                                                        : undefined
                                                }
                                                rel="noopener noreferrer"
                                                className="underline-offset-4 transition hover:text-brand-yellow hover:underline"
                                            >
                                                {text}
                                            </a>
                                        ) : (
                                            <span>{text}</span>
                                        )}
                                    </li>
                                ))}
                            </ul>
                            {info.maps_url && (
                                <a
                                    href={info.maps_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-brand-yellow underline-offset-4 hover:underline"
                                >
                                    <ExternalLink
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                    {t('visit.map')}
                                </a>
                            )}
                        </aside>
                    )}
                </div>
            </section>
        </>
    );
}
