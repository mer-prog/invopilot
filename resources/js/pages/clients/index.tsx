import { Head, Link } from '@inertiajs/react';
import { Plus, Users } from 'lucide-react';
import { ClientTable } from '@/components/clients/client-table';
import { EmptyState } from '@/components/empty-state';
import { Button } from '@/components/ui/button';
import { useTrans } from '@/hooks/use-trans';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Client, PaginatedData } from '@/types';

type Props = {
    clients: PaginatedData<Client>;
};

export default function ClientsIndex({ clients }: Props) {
    const { t } = useTrans();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('clients.title'), href: '/clients' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('clients.title')} />
            <div className="flex flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold tracking-tight">
                        {t('clients.title')}
                    </h1>
                    <Button asChild>
                        <Link href="/clients/create">
                            <Plus className="mr-2 h-4 w-4" />
                            {t('clients.create')}
                        </Link>
                    </Button>
                </div>

                <ClientTable
                    clients={clients.data}
                    emptyContent={
                        <EmptyState
                            icon={Users}
                            title={t('clients.empty_title')}
                            description={t('clients.empty_description')}
                            actionLabel={t('clients.create')}
                            actionHref="/clients/create"
                        />
                    }
                />

                {clients.meta.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm text-muted-foreground">
                        <span>
                            {t('common.showing', {
                                from: clients.meta.from ?? 0,
                                to: clients.meta.to ?? 0,
                                total: clients.meta.total,
                            })}
                        </span>
                        <div className="flex gap-2">
                            {clients.links.prev && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={clients.links.prev}>
                                        &larr;
                                    </Link>
                                </Button>
                            )}
                            {clients.links.next && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={clients.links.next}>
                                        &rarr;
                                    </Link>
                                </Button>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
