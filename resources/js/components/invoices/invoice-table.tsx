import { router, usePage } from '@inertiajs/react';
import { DataTable } from '@/components/data-table';
import { MoneyDisplay } from '@/components/money-display';
import { StatusBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { useTrans } from '@/hooks/use-trans';
import { formatDate } from '@/lib/formatters';
import type { Invoice, InvoiceStatus } from '@/types';

type Filters = {
    status?: string;
    from?: string;
    to?: string;
    sort?: string;
    direction?: string;
};

type Props = {
    invoices: Invoice[];
    filters: Filters;
    emptyContent?: React.ReactNode;
};

const statuses: InvoiceStatus[] = [
    'draft',
    'sent',
    'paid',
    'overdue',
    'cancelled',
];

export function InvoiceTable({ invoices, filters, emptyContent }: Props) {
    const { t } = useTrans();
    const { locale } = usePage().props;

    const applyFilter = (key: string, value: string | undefined) => {
        router.get(
            '/invoices',
            { ...filters, [key]: value || undefined },
            { preserveState: true, preserveScroll: true },
        );
    };

    const columns = [
        {
            key: 'invoice_number',
            label: t('invoices.invoice_number'),
            render: (inv: Invoice) => (
                <span className="font-medium">{inv.invoice_number}</span>
            ),
        },
        {
            key: 'client',
            label: t('invoices.client'),
            className: 'hidden sm:table-cell',
            render: (inv: Invoice) => (
                <span className="text-muted-foreground">
                    {inv.client?.name ?? '—'}
                </span>
            ),
        },
        {
            key: 'status',
            label: t('common.status'),
            render: (inv: Invoice) => <StatusBadge status={inv.status} />,
        },
        {
            key: 'issue_date',
            label: t('invoices.issue_date'),
            className: 'hidden md:table-cell',
            render: (inv: Invoice) => formatDate(inv.issue_date, locale),
        },
        {
            key: 'due_date',
            label: t('invoices.due_date'),
            className: 'hidden lg:table-cell',
            render: (inv: Invoice) => formatDate(inv.due_date, locale),
        },
        {
            key: 'total',
            label: t('common.total'),
            className: 'text-right',
            render: (inv: Invoice) => (
                <span className="text-right font-medium">
                    <MoneyDisplay amount={inv.total} currency={inv.currency} />
                </span>
            ),
        },
    ];

    return (
        <div className="space-y-4">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                <Select
                    value={filters.status ?? 'all'}
                    onValueChange={(v) =>
                        applyFilter('status', v === 'all' ? undefined : v)
                    }
                >
                    <SelectTrigger className="w-full sm:w-44">
                        <SelectValue
                            placeholder={t('invoices.filter_status')}
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{t('common.all')}</SelectItem>
                        {statuses.map((s) => (
                            <SelectItem key={s} value={s}>
                                {t(`invoices.status_${s}`)}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <div className="flex gap-2">
                    <Input
                        type="date"
                        value={filters.from ?? ''}
                        onChange={(e) =>
                            applyFilter('from', e.target.value || undefined)
                        }
                        className="w-full sm:w-40"
                        placeholder={t('invoices.filter_date')}
                    />
                    <Input
                        type="date"
                        value={filters.to ?? ''}
                        onChange={(e) =>
                            applyFilter('to', e.target.value || undefined)
                        }
                        className="w-full sm:w-40"
                    />
                </div>
                {(filters.status || filters.from || filters.to) && (
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={() =>
                            router.get('/invoices', {}, { preserveState: true })
                        }
                    >
                        {t('common.filter')} &times;
                    </Button>
                )}
            </div>

            <DataTable
                columns={columns}
                data={invoices}
                keyExtractor={(inv) => inv.id}
                onRowClick={(inv) => router.visit(`/invoices/${inv.id}`)}
                emptyContent={emptyContent}
            />
        </div>
    );
}
