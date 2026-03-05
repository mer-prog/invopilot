import { Head, Link, router, usePage } from '@inertiajs/react';
import { Copy, Download, Edit, Mail, Trash2 } from 'lucide-react';
import { MoneyDisplay } from '@/components/money-display';
import { StatusBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Separator } from '@/components/ui/separator';
import { useTrans } from '@/hooks/use-trans';
import { formatDate, formatMoney } from '@/lib/formatters';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Invoice, Payment } from '@/types';

type Props = {
    invoice: Invoice & { payments: Payment[] };
};

export default function InvoiceShow({ invoice }: Props) {
    const { t } = useTrans();
    const { locale } = usePage().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('invoices.title'), href: '/invoices' },
        {
            title: invoice.invoice_number,
            href: `/invoices/${invoice.id}`,
        },
    ];

    const handleAction = (action: string) => {
        switch (action) {
            case 'send':
                router.post(`/invoices/${invoice.id}/send`);
                break;
            case 'mark_paid':
                router.post(`/invoices/${invoice.id}/mark-paid`);
                break;
            case 'duplicate':
                router.post(`/invoices/${invoice.id}/duplicate`);
                break;
            case 'delete':
                if (confirm(t('common.confirm_delete'))) {
                    router.delete(`/invoices/${invoice.id}`);
                }
                break;
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head
                title={`${invoice.invoice_number} — ${t('invoices.details')}`}
            />
            <div className="flex flex-col gap-4 p-4">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center gap-3">
                        <h1 className="text-xl font-semibold tracking-tight">
                            {invoice.invoice_number}
                        </h1>
                        <StatusBadge status={invoice.status} />
                    </div>
                    <div className="flex gap-2">
                        {invoice.status === 'draft' && (
                            <>
                                <Button variant="outline" size="sm" asChild>
                                    <Link
                                        href={`/invoices/${invoice.id}/edit`}
                                    >
                                        <Edit className="mr-2 h-4 w-4" />
                                        {t('common.edit')}
                                    </Link>
                                </Button>
                                <Button
                                    size="sm"
                                    onClick={() => handleAction('send')}
                                >
                                    <Mail className="mr-2 h-4 w-4" />
                                    {t('invoices.send')}
                                </Button>
                            </>
                        )}
                        {(invoice.status === 'sent' ||
                            invoice.status === 'overdue') && (
                            <Button
                                size="sm"
                                onClick={() => handleAction('mark_paid')}
                            >
                                {t('invoices.mark_paid')}
                            </Button>
                        )}
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="outline" size="sm">
                                    {t('common.actions')}
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem
                                    onClick={() => handleAction('duplicate')}
                                >
                                    <Copy className="mr-2 h-4 w-4" />
                                    {t('invoices.duplicate')}
                                </DropdownMenuItem>
                                <DropdownMenuItem asChild>
                                    <a
                                        href={`/invoices/${invoice.id}/pdf`}
                                        target="_blank"
                                        rel="noreferrer"
                                    >
                                        <Download className="mr-2 h-4 w-4" />
                                        {t('invoices.download_pdf')}
                                    </a>
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    className="text-destructive"
                                    onClick={() => handleAction('delete')}
                                >
                                    <Trash2 className="mr-2 h-4 w-4" />
                                    {t('common.delete')}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-sm text-muted-foreground">
                                {t('invoices.client')}
                            </div>
                            <div className="mt-1 font-medium">
                                {invoice.client?.name ?? '—'}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-sm text-muted-foreground">
                                {t('invoices.due_date')}
                            </div>
                            <div className="mt-1 font-medium">
                                {formatDate(invoice.due_date, locale)}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-sm text-muted-foreground">
                                {t('common.total')}
                            </div>
                            <div className="mt-1 text-lg font-bold">
                                <MoneyDisplay
                                    amount={invoice.total}
                                    currency={invoice.currency}
                                />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">
                            {t('invoices.items')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b text-xs font-medium uppercase text-muted-foreground">
                                        <th className="pb-2 text-left">
                                            {t('common.description')}
                                        </th>
                                        <th className="pb-2 text-right">
                                            {t('common.quantity')}
                                        </th>
                                        <th className="hidden pb-2 text-right sm:table-cell">
                                            {t('common.unit_price')}
                                        </th>
                                        <th className="hidden pb-2 text-right sm:table-cell">
                                            {t('common.tax_rate')}
                                        </th>
                                        <th className="pb-2 text-right">
                                            {t('common.amount')}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {(invoice.items ?? []).map((item) => (
                                        <tr
                                            key={item.id}
                                            className="border-b last:border-0"
                                        >
                                            <td className="py-2">
                                                {item.description}
                                            </td>
                                            <td className="py-2 text-right">
                                                {item.quantity}
                                            </td>
                                            <td className="hidden py-2 text-right sm:table-cell">
                                                {formatMoney(
                                                    item.unit_price,
                                                    invoice.currency,
                                                    locale,
                                                )}
                                            </td>
                                            <td className="hidden py-2 text-right sm:table-cell">
                                                {item.tax_rate}%
                                            </td>
                                            <td className="py-2 text-right">
                                                {formatMoney(
                                                    item.amount,
                                                    invoice.currency,
                                                    locale,
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <div className="mt-4 flex justify-end">
                            <div className="w-full max-w-xs space-y-1 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        {t('common.subtotal')}
                                    </span>
                                    <MoneyDisplay
                                        amount={invoice.subtotal}
                                        currency={invoice.currency}
                                    />
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        {t('common.tax')}
                                    </span>
                                    <MoneyDisplay
                                        amount={invoice.tax_amount}
                                        currency={invoice.currency}
                                    />
                                </div>
                                {parseFloat(invoice.discount_amount) > 0 && (
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            {t('common.discount')}
                                        </span>
                                        <span>
                                            -
                                            <MoneyDisplay
                                                amount={
                                                    invoice.discount_amount
                                                }
                                                currency={invoice.currency}
                                            />
                                        </span>
                                    </div>
                                )}
                                <Separator />
                                <div className="flex justify-between font-bold">
                                    <span>{t('common.total')}</span>
                                    <MoneyDisplay
                                        amount={invoice.total}
                                        currency={invoice.currency}
                                    />
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {invoice.notes && (
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-sm text-muted-foreground">
                                {t('common.notes')}
                            </div>
                            <p className="mt-1 whitespace-pre-line text-sm">
                                {invoice.notes}
                            </p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
