import { Head, Link } from '@inertiajs/react';
import { FileText, Plus } from 'lucide-react';
import { EmptyState } from '@/components/empty-state';
import { InvoiceTable } from '@/components/invoices/invoice-table';
import { Button } from '@/components/ui/button';
import { useTrans } from '@/hooks/use-trans';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Invoice, PaginatedData } from '@/types';

type Props = {
    invoices: PaginatedData<Invoice>;
    filters: Record<string, string | undefined>;
};

export default function InvoicesIndex({ invoices, filters }: Props) {
    const { t } = useTrans();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('invoices.title'), href: '/invoices' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('invoices.title')} />
            <div className="flex flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold tracking-tight">
                        {t('invoices.title')}
                    </h1>
                    <Button asChild>
                        <Link href="/invoices/create">
                            <Plus className="mr-2 h-4 w-4" />
                            {t('invoices.create')}
                        </Link>
                    </Button>
                </div>

                <InvoiceTable
                    invoices={invoices.data}
                    filters={filters}
                    emptyContent={
                        <EmptyState
                            icon={FileText}
                            title={t('invoices.empty_title')}
                            description={t('invoices.empty_description')}
                            actionLabel={t('invoices.create')}
                            actionHref="/invoices/create"
                        />
                    }
                />

                {invoices.meta.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm text-muted-foreground">
                        <span>
                            {t('common.showing', {
                                from: invoices.meta.from ?? 0,
                                to: invoices.meta.to ?? 0,
                                total: invoices.meta.total,
                            })}
                        </span>
                        <div className="flex gap-2">
                            {invoices.links.prev && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={invoices.links.prev}>
                                        &larr;
                                    </Link>
                                </Button>
                            )}
                            {invoices.links.next && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={invoices.links.next}>
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
