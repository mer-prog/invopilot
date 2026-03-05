import { Head, Link, router } from '@inertiajs/react';
import { Edit, FileText, Trash2 } from 'lucide-react';
import { MoneyDisplay } from '@/components/money-display';
import { StatusBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useTrans } from '@/hooks/use-trans';
import { formatDate } from '@/lib/formatters';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Client, Invoice } from '@/types';
import { usePage } from '@inertiajs/react';

type Props = {
    client: Client & { invoices: Invoice[] };
};

export default function ClientShow({ client }: Props) {
    const { t } = useTrans();
    const { locale } = usePage().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('clients.title'), href: '/clients' },
        { title: client.name, href: `/clients/${client.id}` },
    ];

    const handleDelete = () => {
        if (confirm(t('clients.delete_confirm'))) {
            router.delete(`/clients/${client.id}`);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${client.name} — ${t('clients.details')}`} />
            <div className="flex flex-col gap-4 p-4">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 className="text-xl font-semibold tracking-tight">
                        {client.name}
                    </h1>
                    <div className="flex gap-2">
                        <Button variant="outline" size="sm" asChild>
                            <Link href={`/clients/${client.id}/edit`}>
                                <Edit className="mr-2 h-4 w-4" />
                                {t('common.edit')}
                            </Link>
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            onClick={handleDelete}
                        >
                            <Trash2 className="mr-2 h-4 w-4" />
                            {t('common.delete')}
                        </Button>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">
                                {t('clients.details')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            {client.company && (
                                <div>
                                    <span className="text-muted-foreground">
                                        {t('clients.company')}:
                                    </span>{' '}
                                    {client.company}
                                </div>
                            )}
                            {client.email && (
                                <div>
                                    <span className="text-muted-foreground">
                                        {t('common.email')}:
                                    </span>{' '}
                                    {client.email}
                                </div>
                            )}
                            {client.phone && (
                                <div>
                                    <span className="text-muted-foreground">
                                        {t('common.phone')}:
                                    </span>{' '}
                                    {client.phone}
                                </div>
                            )}
                            {client.address && (
                                <div>
                                    <span className="text-muted-foreground">
                                        {t('common.address')}:
                                    </span>{' '}
                                    <span className="whitespace-pre-line">
                                        {client.address}
                                    </span>
                                </div>
                            )}
                            {client.tax_id && (
                                <div>
                                    <span className="text-muted-foreground">
                                        {t('clients.tax_id')}:
                                    </span>{' '}
                                    {client.tax_id}
                                </div>
                            )}
                            {client.notes && (
                                <div>
                                    <span className="text-muted-foreground">
                                        {t('common.notes')}:
                                    </span>{' '}
                                    {client.notes}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-base">
                            <FileText className="h-4 w-4" />
                            {t('clients.invoice_history')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {client.invoices.length === 0 ? (
                            <p className="py-4 text-center text-sm text-muted-foreground">
                                {t('invoices.empty_title')}
                            </p>
                        ) : (
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>
                                                {t('invoices.invoice_number')}
                                            </TableHead>
                                            <TableHead>
                                                {t('common.status')}
                                            </TableHead>
                                            <TableHead className="hidden sm:table-cell">
                                                {t('invoices.issue_date')}
                                            </TableHead>
                                            <TableHead className="text-right">
                                                {t('common.total')}
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {client.invoices.map((invoice) => (
                                            <TableRow
                                                key={invoice.id}
                                                className="cursor-pointer hover:bg-muted/50"
                                                onClick={() =>
                                                    router.visit(
                                                        `/invoices/${invoice.id}`,
                                                    )
                                                }
                                            >
                                                <TableCell className="font-medium">
                                                    {invoice.invoice_number}
                                                </TableCell>
                                                <TableCell>
                                                    <StatusBadge
                                                        status={invoice.status}
                                                    />
                                                </TableCell>
                                                <TableCell className="hidden sm:table-cell">
                                                    {formatDate(
                                                        invoice.issue_date,
                                                        locale,
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <MoneyDisplay
                                                        amount={invoice.total}
                                                        currency={
                                                            invoice.currency
                                                        }
                                                    />
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
