import { Head, useForm } from '@inertiajs/react';
import {
    RecurringInvoiceForm,
    type RecurringInvoiceFormData,
} from '@/components/recurring-invoices/recurring-invoice-form';
import { useTrans } from '@/hooks/use-trans';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Client, RecurringInvoice } from '@/types';

type Props = {
    recurringInvoice: RecurringInvoice;
    clients: Pick<Client, 'id' | 'name' | 'company'>[];
};

export default function RecurringInvoiceEdit({
    recurringInvoice,
    clients,
}: Props) {
    const { t } = useTrans();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('recurring_invoices.title'),
            href: '/recurring-invoices',
        },
        {
            title: t('recurring_invoices.edit'),
            href: `/recurring-invoices/${recurringInvoice.id}/edit`,
        },
    ];

    const form = useForm<RecurringInvoiceFormData>({
        client_id: String(recurringInvoice.client_id),
        frequency: recurringInvoice.frequency,
        next_issue_date: recurringInvoice.next_issue_date,
        end_date: recurringInvoice.end_date ?? '',
        tax_rate: String(recurringInvoice.tax_rate),
        notes: recurringInvoice.notes ?? '',
        items: recurringInvoice.items.map((item) => ({
            description: item.description,
            quantity: item.quantity,
            unit_price: item.unit_price,
        })),
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.put(`/recurring-invoices/${recurringInvoice.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('recurring_invoices.edit')} />
            <div className="mx-auto max-w-2xl p-4">
                <RecurringInvoiceForm
                    form={form}
                    onSubmit={handleSubmit}
                    clients={clients}
                    submitLabel={t('common.update')}
                />
            </div>
        </AppLayout>
    );
}
