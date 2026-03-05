import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    ClientForm,
    type ClientFormData,
} from '@/components/clients/client-form';
import { useTrans } from '@/hooks/use-trans';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

export default function ClientCreate() {
    const { t } = useTrans();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('clients.title'), href: '/clients' },
        { title: t('clients.create'), href: '/clients/create' },
    ];

    const form = useForm<ClientFormData>({
        name: '',
        email: '',
        phone: '',
        company: '',
        address: '',
        tax_id: '',
        notes: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post('/clients');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('clients.create')} />
            <div className="mx-auto w-full max-w-2xl p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>{t('clients.create')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ClientForm
                            form={form}
                            onSubmit={handleSubmit}
                            submitLabel={t('common.create')}
                        />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
