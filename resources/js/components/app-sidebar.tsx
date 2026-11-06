import { Link, usePage } from '@inertiajs/react';
import {
    BadgeCheck,
    BookOpen,
    CalendarDays,
    CalendarRange,
    LayoutPanelLeft,
    FolderGit2,
    LayoutGrid,
    Wallet,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as events } from '@/routes/admin/events';
import { index as paymentMethods } from '@/routes/admin/payment-methods';
import { index as payments } from '@/routes/admin/payments';
import { index as reservations } from '@/routes/admin/reservations';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const adminNavItems: NavItem[] = [
    {
        title: 'Reservasi',
        href: reservations(),
        icon: CalendarRange,
    },
    {
        title: 'Acara',
        href: events(),
        icon: CalendarDays,
    },
    {
        title: 'Verifikasi pembayaran',
        href: payments(),
        icon: BadgeCheck,
    },
    {
        title: 'Metode pembayaran',
        href: paymentMethods(),
        icon: Wallet,
    },
];

const adminPanelItem: NavItem = {
    title: 'Panel Admin (CRUD)',
    href: '/admin',
    icon: LayoutPanelLeft,
};

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { auth } = usePage().props;
    const isAdmin = auth.user?.role === 'admin';

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
                {isAdmin && <NavMain items={adminNavItems} label="Admin" />}
            </SidebarContent>

            <SidebarFooter>
                <NavFooter
                    items={
                        isAdmin
                            ? [adminPanelItem, ...footerNavItems]
                            : footerNavItems
                    }
                    className="mt-auto"
                />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
