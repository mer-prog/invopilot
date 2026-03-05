import { Head, useForm, usePage } from '@inertiajs/react';
import {
    InvoiceForm,
    type InvoiceFormData,
} from '@/components/invoices/invoice-form';
import { InvoicePreview } from '@/components/invoices/invoice-preview';
import { useTrans } from '@/hooks/use-trans';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Client, Invoice } from '@/types';

type Props = {
    invoice: Invoice;
    clients: Pick<Client, 'id' | 'name' | 'company'>[];
};

export default function InvoiceEdit({ invoice, clients }: Props) {
    const { t } = useTrans();
    const { locale } = usePage().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('invoices.title'), href: '/invoices' },
        {
            title: invoice.invoice_number,
            href: `/invoices/${invoice.id}`,
        },
        { title: t('common.edit'), href: `/invoices/${invoice.id}/edit` },
    ];

    const form = useForm<InvoiceFormData>({
        client_id: invoice.client_id ? String(invoice.client_id) : '',
        issue_date: invoice.issue_date,
        due_date: invoice.due_date,
        currency: invoice.currency,
        discount_amount: invoice.discount_amount,
        notes: invoice.notes ?? '',
        footer: invoice.footer ?? '',
        items: (invoice.items ?? []).map((item, i) => ({
            description: item.description,
            quantity: item.quantity,
            unit_price: item.unit_price,
            tax_rate: item.tax_rate,
            amount: item.amount,
            sort_order: i,
        })),
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.put(`/invoices/${invoice.id}`);
    };

    const selectedClient = clients.find(
        (c) => String(c.id) === form.data.client_id,
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('invoices.edit')} />
            <div className="p-4">
                <div className="grid gap-6 lg:grid-cols-2">
                    <div>
                        <InvoiceForm
                            form={form}
                            onSubmit={handleSubmit}
                            clients={clients}
                            submitLabel={t('common.update')}
                        />
                    </div>
                    <div className="hidden lg:block">
                        <div className="sticky top-4">
                            <h3 className="mb-3 text-sm font-medium text-muted-foreground">
                                {t('invoices.preview')}
                            </h3>
                            <InvoicePreview
                                clientName={selectedClient?.name}
                                issueDate={form.data.issue_date}
                                dueDate={form.data.due_date}
                                currency={form.data.currency}
                                items={form.data.items}
                                discountAmount={
                                    parseFloat(form.data.discount_amount) || 0
                                }
                                notes={form.data.notes}
                                footer={form.data.footer}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
