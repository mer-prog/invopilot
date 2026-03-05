import { Head, useForm, usePage } from '@inertiajs/react';
import {
    InvoiceForm,
    type InvoiceFormData,
} from '@/components/invoices/invoice-form';
import { InvoicePreview } from '@/components/invoices/invoice-preview';
import { useTrans } from '@/hooks/use-trans';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Client } from '@/types';

type Props = {
    clients: Pick<Client, 'id' | 'name' | 'company'>[];
    defaultCurrency: string;
    defaultPaymentTerms: number;
};

export default function InvoiceCreate({
    clients,
    defaultCurrency,
    defaultPaymentTerms,
}: Props) {
    const { t } = useTrans();
    const { locale } = usePage().props;

    const today = new Date().toISOString().slice(0, 10);
    const dueDate = new Date(
        Date.now() + defaultPaymentTerms * 86400000,
    )
        .toISOString()
        .slice(0, 10);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('invoices.title'), href: '/invoices' },
        { title: t('invoices.create'), href: '/invoices/create' },
    ];

    const form = useForm<InvoiceFormData>({
        client_id: '',
        issue_date: today,
        due_date: dueDate,
        currency: defaultCurrency,
        discount_amount: '0',
        notes: '',
        footer: '',
        items: [
            {
                description: '',
                quantity: 1,
                unit_price: 0,
                tax_rate: 0,
                amount: 0,
                sort_order: 0,
            },
        ],
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post('/invoices');
    };

    const selectedClient = clients.find(
        (c) => String(c.id) === form.data.client_id,
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('invoices.create')} />
            <div className="p-4">
                <div className="grid gap-6 lg:grid-cols-2">
                    <div>
                        <InvoiceForm
                            form={form}
                            onSubmit={handleSubmit}
                            clients={clients}
                            submitLabel={t('common.create')}
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
