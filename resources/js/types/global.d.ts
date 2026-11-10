import type { Auth } from '@/types/auth';
import type { Contact } from '@/types/landing';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            locale: 'id' | 'en';
            site: { contact: Contact };
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
