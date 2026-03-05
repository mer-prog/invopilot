import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    ClientForm,
    type ClientFormData,
} from '@/components/clients/client-form';
import { useTrans } from '@/hooks/use-trans';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Client } from '@/types';

type Props = {
    client: Client;
};

export default function ClientEdit({ client }: Props) {
    const { t } = useTrans();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('clients.title'), href: '/clients' },
        { title: client.name, href: `/clients/${client.id}` },
        { title: t('common.edit'), href: `/clients/${client.id}/edit` },
    ];

    const form = useForm<ClientFormData>({
        name: client.name,
        email: client.email ?? '',
        phone: client.phone ?? '',
        company: client.company ?? '',
        address: client.address ?? '',
        tax_id: client.tax_id ?? '',
        notes: client.notes ?? '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.put(`/clients/${client.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('clients.edit')} />
            <div className="mx-auto w-full max-w-2xl p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>{t('clients.edit')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ClientForm
                            form={form}
                            onSubmit={handleSubmit}
                            submitLabel={t('common.update')}
                        />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
