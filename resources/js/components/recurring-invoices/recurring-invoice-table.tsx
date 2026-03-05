import { router } from '@inertiajs/react';
import { MoreHorizontal } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTrans } from '@/hooks/use-trans';
import { formatDate } from '@/lib/formatters';
import type { RecurringInvoice } from '@/types';
import { usePage } from '@inertiajs/react';

type Props = {
    recurringInvoices: RecurringInvoice[];
    emptyContent?: React.ReactNode;
};

export function RecurringInvoiceTable({
    recurringInvoices,
    emptyContent,
}: Props) {
    const { t } = useTrans();
    const { locale } = usePage().props;

    if (recurringInvoices.length === 0 && emptyContent) {
        return <>{emptyContent}</>;
    }

    const handleToggle = (id: number) => {
        router.post(`/recurring-invoices/${id}/toggle`);
    };

    const handleDelete = (id: number) => {
        if (confirm(t('common.confirm_delete'))) {
            router.delete(`/recurring-invoices/${id}`);
        }
    };

    return (
        <div className="overflow-x-auto rounded-md border">
            <table className="w-full text-sm">
                <thead>
                    <tr className="border-b bg-muted/50 text-xs font-medium uppercase text-muted-foreground">
                        <th className="p-3 text-left">
                            {t('invoices.client')}
                        </th>
                        <th className="p-3 text-left">
                            {t('recurring_invoices.frequency')}
                        </th>
                        <th className="hidden p-3 text-left sm:table-cell">
                            {t('recurring_invoices.next_issue_date')}
                        </th>
                        <th className="p-3 text-left">
                            {t('common.status')}
                        </th>
                        <th className="p-3 text-right">
                            {t('common.actions')}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {recurringInvoices.map((ri) => (
                        <tr key={ri.id} className="border-b last:border-0">
                            <td className="p-3">
                                <div className="font-medium">
                                    {ri.client?.name ?? '—'}
                                </div>
                                {ri.client?.company && (
                                    <div className="text-xs text-muted-foreground">
                                        {ri.client.company}
                                    </div>
                                )}
                            </td>
                            <td className="p-3">
                                {t(
                                    `recurring_invoices.${ri.frequency}`,
                                )}
                            </td>
                            <td className="hidden p-3 sm:table-cell">
                                {formatDate(ri.next_issue_date, locale)}
                            </td>
                            <td className="p-3">
                                <Badge
                                    variant={
                                        ri.is_active
                                            ? 'default'
                                            : 'secondary'
                                    }
                                >
                                    {ri.is_active
                                        ? t('recurring_invoices.active')
                                        : t('recurring_invoices.inactive')}
                                </Badge>
                            </td>
                            <td className="p-3 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                        >
                                            <MoreHorizontal className="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            onClick={() =>
                                                router.get(
                                                    `/recurring-invoices/${ri.id}/edit`,
                                                )
                                            }
                                        >
                                            {t('common.edit')}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            onClick={() =>
                                                handleToggle(ri.id)
                                            }
                                        >
                                            {ri.is_active
                                                ? t(
                                                      'recurring_invoices.deactivate',
                                                  )
                                                : t(
                                                      'recurring_invoices.activate',
                                                  )}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            className="text-destructive"
                                            onClick={() =>
                                                handleDelete(ri.id)
                                            }
                                        >
                                            {t('common.delete')}
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
