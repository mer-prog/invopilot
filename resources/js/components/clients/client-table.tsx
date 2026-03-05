import { router } from '@inertiajs/react';
import { DataTable } from '@/components/data-table';
import { MoneyDisplay } from '@/components/money-display';
import { useTrans } from '@/hooks/use-trans';
import type { Client } from '@/types';

type Props = {
    clients: Client[];
    emptyContent?: React.ReactNode;
};

export function ClientTable({ clients, emptyContent }: Props) {
    const { t } = useTrans();

    const columns = [
        {
            key: 'name',
            label: t('common.name'),
            render: (client: Client) => (
                <div>
                    <div className="font-medium">{client.name}</div>
                    {client.company && (
                        <div className="text-sm text-muted-foreground">
                            {client.company}
                        </div>
                    )}
                </div>
            ),
        },
        {
            key: 'email',
            label: t('common.email'),
            className: 'hidden sm:table-cell',
            render: (client: Client) => (
                <span className="text-muted-foreground">
                    {client.email ?? '—'}
                </span>
            ),
        },
        {
            key: 'phone',
            label: t('common.phone'),
            className: 'hidden md:table-cell',
            render: (client: Client) => (
                <span className="text-muted-foreground">
                    {client.phone ?? '—'}
                </span>
            ),
        },
        {
            key: 'invoices_count',
            label: t('clients.total_invoices'),
            className: 'hidden lg:table-cell text-right',
            render: (client: Client) => (
                <span className="text-right">{client.invoices_count ?? 0}</span>
            ),
        },
        {
            key: 'total',
            label: t('clients.total_revenue'),
            className: 'hidden lg:table-cell text-right',
            render: (client: Client) => (
                <span className="text-right">
                    {client.invoices_sum_total ? (
                        <MoneyDisplay amount={client.invoices_sum_total} />
                    ) : (
                        '—'
                    )}
                </span>
            ),
        },
    ];

    return (
        <DataTable
            columns={columns}
            data={clients}
            keyExtractor={(client) => client.id}
            onRowClick={(client) => router.visit(`/clients/${client.id}`)}
            emptyContent={emptyContent}
        />
    );
}
