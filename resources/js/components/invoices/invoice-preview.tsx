import { usePage } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { useTrans } from '@/hooks/use-trans';
import { formatDate, formatMoney } from '@/lib/formatters';
import type { InvoiceItem } from '@/types';

type Props = {
    clientName?: string;
    issueDate: string;
    dueDate: string;
    currency: string;
    items: InvoiceItem[];
    discountAmount: number;
    notes?: string;
    footer?: string;
};

export function InvoicePreview({
    clientName,
    issueDate,
    dueDate,
    currency,
    items,
    discountAmount,
    notes,
    footer,
}: Props) {
    const { t } = useTrans();
    const { locale, currentOrganization } = usePage().props;

    const subtotal = items.reduce(
        (sum, item) => sum + item.quantity * item.unit_price,
        0,
    );
    const taxAmount = items.reduce(
        (sum, item) =>
            sum + item.quantity * item.unit_price * (item.tax_rate / 100),
        0,
    );
    const total = subtotal + taxAmount - discountAmount;

    return (
        <Card className="overflow-hidden">
            <CardContent className="space-y-6 p-6 text-sm">
                <div className="flex items-start justify-between">
                    <div>
                        <h3 className="text-lg font-bold">
                            {currentOrganization?.name}
                        </h3>
                    </div>
                    <div className="text-right">
                        <div className="text-xl font-bold uppercase tracking-wider text-muted-foreground">
                            {t('invoices.singular')}
                        </div>
                    </div>
                </div>

                <Separator />

                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <div className="text-xs font-medium uppercase text-muted-foreground">
                            {t('invoices.bill_to')}
                        </div>
                        <div className="mt-1 font-medium">
                            {clientName || '—'}
                        </div>
                    </div>
                    <div className="text-right">
                        <div className="space-y-1">
                            <div>
                                <span className="text-muted-foreground">
                                    {t('invoices.issue_date')}:{' '}
                                </span>
                                {issueDate
                                    ? formatDate(issueDate, locale)
                                    : '—'}
                            </div>
                            <div>
                                <span className="text-muted-foreground">
                                    {t('invoices.due_date')}:{' '}
                                </span>
                                {dueDate ? formatDate(dueDate, locale) : '—'}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="w-full text-left">
                        <thead>
                            <tr className="border-b text-xs font-medium uppercase text-muted-foreground">
                                <th className="pb-2">
                                    {t('common.description')}
                                </th>
                                <th className="pb-2 text-right">
                                    {t('common.quantity')}
                                </th>
                                <th className="pb-2 text-right">
                                    {t('common.unit_price')}
                                </th>
                                <th className="pb-2 text-right">
                                    {t('common.tax_rate')}
                                </th>
                                <th className="pb-2 text-right">
                                    {t('common.amount')}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {items
                                .filter((i) => i.description)
                                .map((item, idx) => {
                                    const lineTotal =
                                        item.quantity *
                                        item.unit_price *
                                        (1 + item.tax_rate / 100);
                                    return (
                                        <tr
                                            key={idx}
                                            className="border-b last:border-0"
                                        >
                                            <td className="py-2">
                                                {item.description}
                                            </td>
                                            <td className="py-2 text-right">
                                                {item.quantity}
                                            </td>
                                            <td className="py-2 text-right">
                                                {formatMoney(
                                                    item.unit_price,
                                                    currency,
                                                    locale,
                                                )}
                                            </td>
                                            <td className="py-2 text-right">
                                                {item.tax_rate}%
                                            </td>
                                            <td className="py-2 text-right">
                                                {formatMoney(
                                                    lineTotal,
                                                    currency,
                                                    locale,
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })}
                        </tbody>
                    </table>
                </div>

                <div className="flex justify-end">
                    <div className="w-full max-w-xs space-y-1">
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                {t('common.subtotal')}
                            </span>
                            <span>
                                {formatMoney(subtotal, currency, locale)}
                            </span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                {t('common.tax')}
                            </span>
                            <span>
                                {formatMoney(taxAmount, currency, locale)}
                            </span>
                        </div>
                        {discountAmount > 0 && (
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">
                                    {t('common.discount')}
                                </span>
                                <span>
                                    -
                                    {formatMoney(
                                        discountAmount,
                                        currency,
                                        locale,
                                    )}
                                </span>
                            </div>
                        )}
                        <Separator />
                        <div className="flex justify-between text-base font-bold">
                            <span>{t('common.total')}</span>
                            <span>
                                {formatMoney(total, currency, locale)}
                            </span>
                        </div>
                    </div>
                </div>

                {notes && (
                    <div>
                        <div className="text-xs font-medium uppercase text-muted-foreground">
                            {t('common.notes')}
                        </div>
                        <p className="mt-1 whitespace-pre-line">{notes}</p>
                    </div>
                )}

                {footer && (
                    <div className="border-t pt-4 text-center text-xs text-muted-foreground">
                        {footer}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
