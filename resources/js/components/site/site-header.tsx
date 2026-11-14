import { Link, usePage } from '@inertiajs/react';
import {
    ChevronDown,
    Clock,
    Mail,
    MapPin,
    Menu,
    Phone,
    X,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import PublicUserMenu from '@/components/public-user-menu';
import LanguageSwitcher, {
    changeLocale,
} from '@/components/site/language-switcher';
import {
    localeNames,
    useTranslation,
    type Locale,
    type MessageKey,
} from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { home, login } from '@/routes';
import events from '@/routes/events';
import facilities from '@/routes/facilities';

type NavLink = { label: MessageKey; href: string };
type NavColumn = { heading: MessageKey; links: NavLink[] };
type NavItem = { label: MessageKey; href?: string; columns?: NavColumn[] };

/**
 * Related pages are grouped so the bar stays short: each group opens a panel with titled columns.
 */
const navigation: NavItem[] = [
    { label: 'nav.home', href: home().url },
    {
        label: 'nav.explore',
        columns: [
            {
                heading: 'nav.explore.tourism',
                links: [
                    { label: 'nav.destinations', href: '/#destinasi' },
                    { label: 'nav.activities', href: '/#pengalaman' },
                ],
            },
            {
                heading: 'nav.explore.village',
                links: [
                    { label: 'nav.products', href: '/#produk' },
                    { label: 'nav.stories', href: '/#cerita' },
                ],
            },
        ],
    },
    {
        label: 'nav.services',
        columns: [
            {
                heading: 'nav.services.booking',
                links: [
                    { label: 'nav.facilities', href: facilities.index().url },
                    { label: 'nav.events', href: events.index().url },
                ],
            },
        ],
    },
    {
        label: 'nav.info',
        columns: [
            {
                heading: 'nav.info.visit',
                links: [
                    { label: 'nav.visitInfo', href: '/#kunjungan' },
                    { label: 'nav.faq', href: '/#kunjungan' },
                ],
            },
            {
                heading: 'nav.info.contact',
                links: [{ label: 'nav.contact', href: '/#kontak' }],
            },
        ],
    },
];

const pathOf = (href: string): string => href.split('#')[0] || '/';

function NavAnchor({
    href,
    className,
    onClick,
    children,
}: {
    href: string;
    className?: string;
    onClick?: () => void;
    children: React.ReactNode;
}) {
    // Anchors on the home page are plain links so the browser scrolls to the section.
    return href.includes('#') ? (
        <a href={href} className={className} onClick={onClick}>
            {children}
        </a>
    ) : (
        <Link href={href} className={className} onClick={onClick}>
            {children}
        </Link>
    );
}

function isActive(item: NavItem, currentPath: string): boolean {
    if (item.href) {
        return pathOf(item.href) === currentPath;
    }

    return (item.columns ?? []).some((column) =>
        column.links.some((link) => {
            const path = pathOf(link.href);

            return path !== '/' && currentPath.startsWith(path);
        }),
    );
}

export default function SiteHeader() {
    const page = usePage();
    const { auth, site } = page.props;
    const { t, locale } = useTranslation();
    const contact = site.contact;
    const currentPath = pathOf(page.url.split('?')[0]);

    const [openMenu, setOpenMenu] = useState<MessageKey | null>(null);
    const [mobileOpen, setMobileOpen] = useState(false);
    const headerRef = useRef<HTMLElement>(null);
    const closeTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

    const openNow = (label: MessageKey) => {
        if (closeTimer.current) {
            clearTimeout(closeTimer.current);
        }
        setOpenMenu(label);
    };

    const closeSoon = () => {
        closeTimer.current = setTimeout(() => setOpenMenu(null), 150);
    };

    useEffect(() => {
        const onKey = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setOpenMenu(null);
                setMobileOpen(false);
            }
        };
        const onClickOutside = (event: MouseEvent) => {
            if (!headerRef.current?.contains(event.target as Node)) {
                setOpenMenu(null);
            }
        };

        document.addEventListener('keydown', onKey);
        document.addEventListener('mousedown', onClickOutside);

        return () => {
            document.removeEventListener('keydown', onKey);
            document.removeEventListener('mousedown', onClickOutside);
        };
    }, []);

    const phoneHref = contact.phone
        ? `tel:${contact.phone.replace(/[^\d+]/g, '')}`
        : undefined;

    const topBarItems = [
        contact.address && {
            icon: MapPin,
            text: contact.address,
            href: contact.maps_url,
        },
        contact.hours && { icon: Clock, text: contact.hours },
        contact.phone && { icon: Phone, text: contact.phone, href: phoneHref },
        contact.email && {
            icon: Mail,
            text: contact.email,
            href: `mailto:${contact.email}`,
        },
    ].filter(Boolean) as {
        icon: typeof MapPin;
        text: string;
        href?: string;
    }[];

    const openItem = navigation.find((item) => item.label === openMenu);

    return (
        <header
            ref={headerRef}
            className="sticky top-0 z-50"
            onMouseLeave={closeSoon}
            onMouseEnter={() =>
                closeTimer.current && clearTimeout(closeTimer.current)
            }
        >
            {topBarItems.length > 0 && (
                <div className="hidden bg-forest-900 text-xs text-forest-100 md:block">
                    <div className="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-1 px-4 py-2">
                        {topBarItems.map(({ icon: Icon, text, href }) => {
                            const inner = (
                                <>
                                    <Icon
                                        className="size-3.5 opacity-80"
                                        aria-hidden="true"
                                    />
                                    {text}
                                </>
                            );

                            return href ? (
                                <a
                                    key={text}
                                    href={href}
                                    target={
                                        href.startsWith('http')
                                            ? '_blank'
                                            : undefined
                                    }
                                    rel="noopener noreferrer"
                                    className="flex items-center gap-1.5 transition hover:text-cream"
                                >
                                    {inner}
                                </a>
                            ) : (
                                <span
                                    key={text}
                                    className="flex items-center gap-1.5"
                                >
                                    {inner}
                                </span>
                            );
                        })}
                    </div>
                </div>
            )}

            <div className="relative border-b border-forest-100 bg-cream/95 backdrop-blur">
                <div className="mx-auto flex max-w-6xl items-center gap-8 px-4 py-3">
                    <Link
                        href={home()}
                        aria-label="SIWAPAS Pasirnanjung Geulis"
                        className="flex shrink-0 items-center"
                    >
                        <img
                            src="/images/brand/logo-navbar.png"
                            alt=""
                            width={300}
                            height={101}
                            className="h-11 w-auto md:h-12"
                        />
                    </Link>

                    <nav
                        className="mx-auto hidden items-stretch gap-9 xl:flex"
                        aria-label="Utama"
                    >
                        {navigation.map((item) => {
                            const active = isActive(item, currentPath);
                            const classes = cn(
                                'flex items-center gap-1.5 border-b-2 py-3 text-[13px] font-semibold tracking-[0.18em] text-forest-900 uppercase transition-colors hover:text-clay',
                                active || openMenu === item.label
                                    ? 'border-clay'
                                    : 'border-transparent',
                            );

                            return item.columns ? (
                                <button
                                    key={item.label}
                                    type="button"
                                    aria-expanded={openMenu === item.label}
                                    aria-haspopup="true"
                                    className={classes}
                                    onMouseEnter={() => openNow(item.label)}
                                    onFocus={() => openNow(item.label)}
                                    onClick={() =>
                                        setOpenMenu(
                                            openMenu === item.label
                                                ? null
                                                : item.label,
                                        )
                                    }
                                >
                                    {t(item.label)}
                                    <ChevronDown
                                        className={cn(
                                            'size-3.5 transition-transform',
                                            openMenu === item.label &&
                                                'rotate-180',
                                        )}
                                    />
                                </button>
                            ) : (
                                <Link
                                    key={item.label}
                                    href={item.href as string}
                                    className={classes}
                                    onMouseEnter={() => setOpenMenu(null)}
                                >
                                    {t(item.label)}
                                </Link>
                            );
                        })}
                    </nav>

                    <div className="ml-auto flex items-center gap-6 xl:ml-0 xl:pl-4">
                        <div className="hidden sm:block">
                            <LanguageSwitcher />
                        </div>
                        {auth.user ? (
                            <PublicUserMenu user={auth.user} />
                        ) : (
                            <Link
                                href={login()}
                                className="hidden px-2 text-sm font-medium text-forest-900 hover:text-clay md:block"
                            >
                                {t('header.login')}
                            </Link>
                        )}
                        <Link
                            href={facilities.index()}
                            className="hidden rounded-md bg-clay-dark px-6 py-2.5 text-xs font-semibold tracking-wide text-white uppercase transition hover:bg-clay md:block"
                        >
                            {t('header.book')}
                        </Link>
                        <button
                            type="button"
                            aria-label={
                                mobileOpen
                                    ? t('header.menuClose')
                                    : t('header.menuOpen')
                            }
                            aria-expanded={mobileOpen}
                            onClick={() => setMobileOpen(!mobileOpen)}
                            className="rounded-md p-2 text-forest-900 xl:hidden"
                        >
                            {mobileOpen ? (
                                <X className="size-6" />
                            ) : (
                                <Menu className="size-6" />
                            )}
                        </button>
                    </div>
                </div>

                {openItem?.columns && (
                    <div
                        className="absolute inset-x-0 top-full hidden border-t border-forest-100 bg-white shadow-lg xl:block"
                        onMouseEnter={() => openNow(openItem.label)}
                    >
                        <div className="mx-auto flex max-w-6xl gap-20 px-4 py-10">
                            {openItem.columns.map((column) => (
                                <div key={column.heading} className="min-w-48">
                                    <h3 className="mb-5 font-serif text-sm font-semibold tracking-[0.2em] text-clay-dark uppercase">
                                        {t(column.heading)}
                                    </h3>
                                    <ul className="space-y-3">
                                        {column.links.map((link) => (
                                            <li key={link.label}>
                                                <NavAnchor
                                                    href={link.href}
                                                    onClick={() =>
                                                        setOpenMenu(null)
                                                    }
                                                    className="text-forest-800/90 transition hover:text-clay"
                                                >
                                                    {t(link.label)}
                                                </NavAnchor>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {mobileOpen && (
                    <nav className="max-h-[70vh] overflow-y-auto border-t border-forest-100 bg-cream px-4 py-3 xl:hidden">
                        <div className="mx-auto flex max-w-6xl flex-col">
                            {navigation.map((item) =>
                                item.columns ? (
                                    <details
                                        key={item.label}
                                        className="group border-b border-forest-100"
                                    >
                                        <summary className="flex cursor-pointer list-none items-center justify-between py-3 text-sm font-semibold tracking-[0.15em] text-forest-900 uppercase">
                                            {t(item.label)}
                                            <ChevronDown className="size-4 transition group-open:rotate-180" />
                                        </summary>
                                        <div className="space-y-4 pb-4 pl-2">
                                            {item.columns.map((column) => (
                                                <div key={column.heading}>
                                                    <p className="mb-2 text-xs font-semibold tracking-[0.2em] text-clay-dark uppercase">
                                                        {t(column.heading)}
                                                    </p>
                                                    <ul className="space-y-2">
                                                        {column.links.map(
                                                            (link) => (
                                                                <li
                                                                    key={
                                                                        link.label
                                                                    }
                                                                >
                                                                    <NavAnchor
                                                                        href={
                                                                            link.href
                                                                        }
                                                                        onClick={() =>
                                                                            setMobileOpen(
                                                                                false,
                                                                            )
                                                                        }
                                                                        className="text-sm text-forest-800"
                                                                    >
                                                                        {t(
                                                                            link.label,
                                                                        )}
                                                                    </NavAnchor>
                                                                </li>
                                                            ),
                                                        )}
                                                    </ul>
                                                </div>
                                            ))}
                                        </div>
                                    </details>
                                ) : (
                                    <Link
                                        key={item.label}
                                        href={item.href as string}
                                        onClick={() => setMobileOpen(false)}
                                        className="border-b border-forest-100 py-3 text-sm font-semibold tracking-[0.15em] text-forest-900 uppercase"
                                    >
                                        {t(item.label)}
                                    </Link>
                                ),
                            )}

                            <div className="flex gap-2 py-4">
                                {(['id', 'en'] as Locale[]).map((code) => (
                                    <button
                                        key={code}
                                        type="button"
                                        onClick={() => changeLocale(code)}
                                        className={cn(
                                            'rounded-full border px-4 py-1.5 text-xs font-semibold tracking-wider uppercase',
                                            code === locale
                                                ? 'border-clay-dark bg-clay-dark text-white'
                                                : 'border-forest-200 text-forest-900',
                                        )}
                                    >
                                        {localeNames[code]}
                                    </button>
                                ))}
                            </div>

                            {!auth.user && (
                                <Link
                                    href={login()}
                                    className="py-2 text-sm font-medium text-forest-900"
                                >
                                    {t('header.login')}
                                </Link>
                            )}
                            <Link
                                href={facilities.index()}
                                className="mt-2 rounded-md bg-clay-dark px-4 py-2.5 text-center text-xs font-semibold tracking-wide text-white uppercase"
                            >
                                {t('header.book')}
                            </Link>
                        </div>
                    </nav>
                )}
            </div>
        </header>
    );
}
