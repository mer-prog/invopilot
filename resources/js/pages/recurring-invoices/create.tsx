import { Head, useForm } from '@inertiajs/react';
import {
    RecurringInvoiceForm,
    type RecurringInvoiceFormData,
} from '@/components/recurring-invoices/recurring-invoice-form';
import { useTrans } from '@/hooks/use-trans';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Client } from '@/types';

type Props = {
    clients: Pick<Client, 'id' | 'name' | 'company'>[];
};

export default function RecurringInvoiceCreate({ clients }: Props) {
    const { t } = useTrans();

    const tomorrow = new Date(Date.now() + 86400000)
        .toISOString()
        .slice(0, 10);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('recurring_invoices.title'),
            href: '/recurring-invoices',
        },
        {
            title: t('recurring_invoices.create'),
            href: '/recurring-invoices/create',
        },
    ];

    const form = useForm<RecurringInvoiceFormData>({
        client_id: '',
        frequency: 'monthly',
        next_issue_date: tomorrow,
        end_date: '',
        tax_rate: '10',
        notes: '',
        items: [{ description: '', quantity: 1, unit_price: 0 }],
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post('/recurring-invoices');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('recurring_invoices.create')} />
            <div className="mx-auto max-w-2xl p-4">
                <RecurringInvoiceForm
                    form={form}
                    onSubmit={handleSubmit}
                    clients={clients}
                    submitLabel={t('common.create')}
                />
            </div>
        </AppLayout>
    );
}
