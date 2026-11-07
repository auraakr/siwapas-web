import { Link } from '@inertiajs/react';
import { ChevronDown, LayoutDashboard } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { UserMenuContent } from '@/components/user-menu-content';
import { dashboard } from '@/routes';
import type { User } from '@/types';

export default function PublicUserMenu({ user }: { user: User }) {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <button
                    type="button"
                    className="flex items-center gap-2 rounded-md px-2 py-1 text-sm hover:bg-accent"
                    data-test="public-user-menu"
                >
                    <UserInfo user={user} />
                    <ChevronDown className="size-4 opacity-60" />
                </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="min-w-56">
                <UserMenuContent
                    user={user}
                    extraItems={
                        <DropdownMenuItem asChild>
                            {user.role === 'admin' ? (
                                <a
                                    className="block w-full cursor-pointer"
                                    href="/admin"
                                >
                                    <LayoutDashboard className="mr-2" />
                                    Dashboard Admin
                                </a>
                            ) : (
                                <Link
                                    className="block w-full cursor-pointer"
                                    href={dashboard()}
                                >
                                    <LayoutDashboard className="mr-2" />
                                    Dashboard
                                </Link>
                            )}
                        </DropdownMenuItem>
                    }
                />
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
