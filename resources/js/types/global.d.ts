import type { Auth } from '@/types/auth';

type TranslationGroup = Record<string, string>;

type Organization = {
    id: number;
    name: string;
    slug: string;
    logo_url: string | null;
    default_currency: string;
    invoice_prefix: string;
    default_payment_terms: number;
};

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            locale: string;
            translations: Record<string, TranslationGroup>;
            currentOrganization: Organization | null;
            flash: {
                success: string | null;
                error: string | null;
            };
            [key: string]: unknown;
        };
    }
}
