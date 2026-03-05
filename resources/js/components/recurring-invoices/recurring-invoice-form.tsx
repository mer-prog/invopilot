import type { InertiaFormProps } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useTrans } from '@/hooks/use-trans';
import type { Client, Frequency, RecurringInvoiceItem } from '@/types';

export type RecurringInvoiceFormData = {
    client_id: string;
    frequency: Frequency;
    next_issue_date: string;
    end_date: string;
    tax_rate: string;
    notes: string;
    items: RecurringInvoiceItem[];
};

type Props = {
    form: InertiaFormProps<RecurringInvoiceFormData>;
    onSubmit: (e: React.FormEvent) => void;
    clients: Pick<Client, 'id' | 'name' | 'company'>[];
    submitLabel: string;
};

const frequencies: Frequency[] = [
    'weekly',
    'biweekly',
    'monthly',
    'quarterly',
    'yearly',
];

export function RecurringInvoiceForm({
    form,
    onSubmit,
    clients,
    submitLabel,
}: Props) {
    const { t } = useTrans();

    const addItem = () => {
        form.setData('items', [
            ...form.data.items,
            { description: '', quantity: 1, unit_price: 0 },
        ]);
    };

    const removeItem = (index: number) => {
        form.setData(
            'items',
            form.data.items.filter((_, i) => i !== index),
        );
    };

    const updateItem = (
        index: number,
        field: keyof RecurringInvoiceItem,
        value: string | number,
    ) => {
        const items = [...form.data.items];
        items[index] = { ...items[index], [field]: value };
        form.setData('items', items);
    };

    return (
        <form onSubmit={onSubmit}>
            <Card>
                <CardHeader>
                    <CardTitle className="text-base">
                        {t('recurring_invoices.singular')}
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="client_id">
                                {t('invoices.client')}
                            </Label>
                            <Select
                                value={form.data.client_id}
                                onValueChange={(v) =>
                                    form.setData('client_id', v)
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {clients.map((client) => (
                                        <SelectItem
                                            key={client.id}
                                            value={String(client.id)}
                                        >
                                            {client.name}
                                            {client.company &&
                                                ` (${client.company})`}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {form.errors.client_id && (
                                <p className="text-sm text-destructive">
                                    {form.errors.client_id}
                                </p>
                            )}
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="frequency">
                                {t('recurring_invoices.frequency')}
                            </Label>
                            <Select
                                value={form.data.frequency}
                                onValueChange={(v) =>
                                    form.setData('frequency', v as Frequency)
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {frequencies.map((f) => (
                                        <SelectItem key={f} value={f}>
                                            {t(`recurring_invoices.${f}`)}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="next_issue_date">
                                {t('recurring_invoices.next_issue_date')}
                            </Label>
                            <Input
                                id="next_issue_date"
                                type="date"
                                value={form.data.next_issue_date}
                                onChange={(e) =>
                                    form.setData(
                                        'next_issue_date',
                                        e.target.value,
                                    )
                                }
                            />
                            {form.errors.next_issue_date && (
                                <p className="text-sm text-destructive">
                                    {form.errors.next_issue_date}
                                </p>
                            )}
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="end_date">
                                {t('recurring_invoices.end_date')}
                            </Label>
                            <Input
                                id="end_date"
                                type="date"
                                value={form.data.end_date}
                                onChange={(e) =>
                                    form.setData('end_date', e.target.value)
                                }
                            />
                        </div>
                    </div>

                    <div className="grid gap-2 sm:max-w-[200px]">
                        <Label htmlFor="tax_rate">
                            {t('recurring_invoices.tax_rate')} (%)
                        </Label>
                        <Input
                            id="tax_rate"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            value={form.data.tax_rate}
                            onChange={(e) =>
                                form.setData('tax_rate', e.target.value)
                            }
                        />
                    </div>

                    <div className="space-y-3">
                        <div className="flex items-center justify-between">
                            <Label>{t('invoices.items')}</Label>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={addItem}
                            >
                                <Plus className="mr-1 h-4 w-4" />
                                {t('invoices.add_item')}
                            </Button>
                        </div>
                        {form.data.items.map((item, index) => (
                            <div
                                key={index}
                                className="grid gap-2 rounded-md border p-3 sm:grid-cols-[1fr_80px_100px_auto]"
                            >
                                <div className="grid gap-1">
                                    <Label className="text-xs">
                                        {t('common.description')}
                                    </Label>
                                    <Input
                                        value={item.description}
                                        onChange={(e) =>
                                            updateItem(
                                                index,
                                                'description',
                                                e.target.value,
                                            )
                                        }
                                    />
                                </div>
                                <div className="grid gap-1">
                                    <Label className="text-xs">
                                        {t('common.quantity')}
                                    </Label>
                                    <Input
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        value={item.quantity}
                                        onChange={(e) =>
                                            updateItem(
                                                index,
                                                'quantity',
                                                parseFloat(e.target.value) ||
                                                    0,
                                            )
                                        }
                                    />
                                </div>
                                <div className="grid gap-1">
                                    <Label className="text-xs">
                                        {t('common.unit_price')}
                                    </Label>
                                    <Input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={item.unit_price}
                                        onChange={(e) =>
                                            updateItem(
                                                index,
                                                'unit_price',
                                                parseFloat(e.target.value) ||
                                                    0,
                                            )
                                        }
                                    />
                                </div>
                                <div className="flex items-end">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => removeItem(index)}
                                        disabled={form.data.items.length <= 1}
                                    >
                                        <Trash2 className="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </div>
                        ))}
                        {form.errors.items && (
                            <p className="text-sm text-destructive">
                                {form.errors.items}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="notes">{t('common.notes')}</Label>
                        <Textarea
                            id="notes"
                            value={form.data.notes}
                            onChange={(e) =>
                                form.setData('notes', e.target.value)
                            }
                            rows={3}
                        />
                    </div>

                    <div className="flex justify-end">
                        <Button type="submit" disabled={form.processing}>
                            {submitLabel}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </form>
    );
}
