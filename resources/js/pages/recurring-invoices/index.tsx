import { Head, Link } from '@inertiajs/react';
import { Plus, RefreshCw } from 'lucide-react';
import { EmptyState } from '@/components/empty-state';
import { RecurringInvoiceTable } from '@/components/recurring-invoices/recurring-invoice-table';
import { Button } from '@/components/ui/button';
import { useTrans } from '@/hooks/use-trans';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, PaginatedData, RecurringInvoice } from '@/types';

type Props = {
    recurringInvoices: PaginatedData<RecurringInvoice>;
};

export default function RecurringInvoicesIndex({
    recurringInvoices,
}: Props) {
    const { t } = useTrans();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('recurring_invoices.title'),
            href: '/recurring-invoices',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('recurring_invoices.title')} />
            <div className="flex flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold tracking-tight">
                        {t('recurring_invoices.title')}
                    </h1>
                    <Button asChild>
                        <Link href="/recurring-invoices/create">
                            <Plus className="mr-2 h-4 w-4" />
                            {t('recurring_invoices.create')}
                        </Link>
                    </Button>
                </div>

                <RecurringInvoiceTable
                    recurringInvoices={recurringInvoices.data}
                    emptyContent={
                        <EmptyState
                            icon={RefreshCw}
                            title={t('recurring_invoices.empty_title')}
                            description={t(
                                'recurring_invoices.empty_description',
                            )}
                            actionLabel={t('recurring_invoices.create')}
                            actionHref="/recurring-invoices/create"
                        />
                    }
                />

                {recurringInvoices.meta.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm text-muted-foreground">
                        <span>
                            {t('common.showing', {
                                from: recurringInvoices.meta.from ?? 0,
                                to: recurringInvoices.meta.to ?? 0,
                                total: recurringInvoices.meta.total,
                            })}
                        </span>
                        <div className="flex gap-2">
                            {recurringInvoices.links.prev && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link
                                        href={recurringInvoices.links.prev}
                                    >
                                        &larr;
                                    </Link>
                                </Button>
                            )}
                            {recurringInvoices.links.next && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link
                                        href={recurringInvoices.links.next}
                                    >
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
