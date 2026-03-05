import type { InertiaFormProps } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import {
    closestCenter,
    DndContext,
    type DragEndEvent,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import {
    arrayMove,
    SortableContext,
    sortableKeyboardCoordinates,
    useSortable,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical, Plus, Trash2 } from 'lucide-react';
import InputError from '@/components/input-error';
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
import { formatMoney } from '@/lib/formatters';
import type { Client, InvoiceItem } from '@/types';

type InvoiceFormData = {
    client_id: string;
    issue_date: string;
    due_date: string;
    currency: string;
    discount_amount: string;
    notes: string;
    footer: string;
    items: InvoiceItem[];
};

type Props = {
    form: InertiaFormProps<InvoiceFormData>;
    onSubmit: (e: React.FormEvent) => void;
    clients: Pick<Client, 'id' | 'name' | 'company'>[];
    submitLabel: string;
};

export type { InvoiceFormData };

function SortableItem({
    item,
    index,
    onUpdate,
    onRemove,
    t,
    currency,
    locale,
}: {
    item: InvoiceItem;
    index: number;
    onUpdate: (index: number, field: keyof InvoiceItem, value: string | number) => void;
    onRemove: (index: number) => void;
    t: (key: string) => string;
    currency: string;
    locale: string;
}) {
    const { attributes, listeners, setNodeRef, transform, transition } =
        useSortable({ id: item.sort_order });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
    };

    const lineTotal =
        item.quantity * item.unit_price * (1 + item.tax_rate / 100);

    return (
        <div
            ref={setNodeRef}
            style={style}
            className="flex flex-col gap-3 rounded-md border p-3 sm:flex-row sm:items-start"
        >
            <button
                type="button"
                className="hidden cursor-grab touch-none sm:block"
                {...attributes}
                {...listeners}
            >
                <GripVertical className="h-5 w-5 text-muted-foreground" />
            </button>
            <div className="grid flex-1 gap-3 sm:grid-cols-12">
                <div className="sm:col-span-4">
                    <Input
                        placeholder={t('common.description')}
                        value={item.description}
                        onChange={(e) =>
                            onUpdate(index, 'description', e.target.value)
                        }
                    />
                </div>
                <div className="sm:col-span-2">
                    <Input
                        type="number"
                        placeholder={t('common.quantity')}
                        value={item.quantity || ''}
                        onChange={(e) =>
                            onUpdate(
                                index,
                                'quantity',
                                parseFloat(e.target.value) || 0,
                            )
                        }
                        min="0.01"
                        step="0.01"
                    />
                </div>
                <div className="sm:col-span-2">
                    <Input
                        type="number"
                        placeholder={t('common.unit_price')}
                        value={item.unit_price || ''}
                        onChange={(e) =>
                            onUpdate(
                                index,
                                'unit_price',
                                parseFloat(e.target.value) || 0,
                            )
                        }
                        min="0"
                        step="0.01"
                    />
                </div>
                <div className="sm:col-span-1">
                    <Input
                        type="number"
                        placeholder="%"
                        value={item.tax_rate || ''}
                        onChange={(e) =>
                            onUpdate(
                                index,
                                'tax_rate',
                                parseFloat(e.target.value) || 0,
                            )
                        }
                        min="0"
                        max="100"
                        step="0.01"
                    />
                </div>
                <div className="flex items-center justify-between sm:col-span-3">
                    <span className="text-sm font-medium">
                        {formatMoney(lineTotal, currency, locale)}
                    </span>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        className="h-8 w-8 shrink-0 text-destructive"
                        onClick={() => onRemove(index)}
                    >
                        <Trash2 className="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    );
}

export function InvoiceForm({
    form,
    onSubmit,
    clients,
    submitLabel,
}: Props) {
    const { t } = useTrans();
    const { locale } = usePage().props;

    const sensors = useSensors(
        useSensor(PointerSensor),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        }),
    );

    const addItem = () => {
        form.setData('items', [
            ...form.data.items,
            {
                description: '',
                quantity: 1,
                unit_price: 0,
                tax_rate: 0,
                amount: 0,
                sort_order: form.data.items.length,
            },
        ]);
    };

    const removeItem = (index: number) => {
        const items = form.data.items
            .filter((_, i) => i !== index)
            .map((item, i) => ({ ...item, sort_order: i }));
        form.setData('items', items);
    };

    const updateItem = (
        index: number,
        field: keyof InvoiceItem,
        value: string | number,
    ) => {
        const items = [...form.data.items];
        items[index] = { ...items[index], [field]: value };
        form.setData('items', items);
    };

    const handleDragEnd = (event: DragEndEvent) => {
        const { active, over } = event;
        if (over && active.id !== over.id) {
            const oldIndex = form.data.items.findIndex(
                (i) => i.sort_order === active.id,
            );
            const newIndex = form.data.items.findIndex(
                (i) => i.sort_order === over.id,
            );
            const reordered = arrayMove(form.data.items, oldIndex, newIndex).map(
                (item, i) => ({ ...item, sort_order: i }),
            );
            form.setData('items', reordered);
        }
    };

    return (
        <form onSubmit={onSubmit} className="space-y-6">
            <Card>
                <CardContent className="grid gap-4 pt-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div className="space-y-2">
                        <Label>{t('invoices.client')}</Label>
                        <Select
                            value={form.data.client_id}
                            onValueChange={(v) => form.setData('client_id', v)}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="—" />
                            </SelectTrigger>
                            <SelectContent>
                                {clients.map((c) => (
                                    <SelectItem
                                        key={c.id}
                                        value={String(c.id)}
                                    >
                                        {c.name}
                                        {c.company ? ` (${c.company})` : ''}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={form.errors.client_id} />
                    </div>

                    <div className="space-y-2">
                        <Label>{t('invoices.issue_date')}</Label>
                        <Input
                            type="date"
                            value={form.data.issue_date}
                            onChange={(e) =>
                                form.setData('issue_date', e.target.value)
                            }
                        />
                        <InputError message={form.errors.issue_date} />
                    </div>

                    <div className="space-y-2">
                        <Label>{t('invoices.due_date')}</Label>
                        <Input
                            type="date"
                            value={form.data.due_date}
                            onChange={(e) =>
                                form.setData('due_date', e.target.value)
                            }
                        />
                        <InputError message={form.errors.due_date} />
                    </div>

                    <div className="space-y-2">
                        <Label>{t('invoices.currency')}</Label>
                        <Select
                            value={form.data.currency}
                            onValueChange={(v) => form.setData('currency', v)}
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {[
                                    'USD',
                                    'EUR',
                                    'GBP',
                                    'JPY',
                                    'CAD',
                                    'AUD',
                                ].map((c) => (
                                    <SelectItem key={c} value={c}>
                                        {c}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={form.errors.currency} />
                    </div>

                    <div className="space-y-2">
                        <Label>{t('common.discount')}</Label>
                        <Input
                            type="number"
                            value={form.data.discount_amount}
                            onChange={(e) =>
                                form.setData('discount_amount', e.target.value)
                            }
                            min="0"
                            step="0.01"
                        />
                        <InputError message={form.errors.discount_amount} />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle className="text-base">
                        {t('invoices.items')}
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                    <div className="hidden text-xs font-medium text-muted-foreground sm:grid sm:grid-cols-12 sm:gap-3 sm:pl-10">
                        <div className="col-span-4">
                            {t('common.description')}
                        </div>
                        <div className="col-span-2">{t('common.quantity')}</div>
                        <div className="col-span-2">
                            {t('common.unit_price')}
                        </div>
                        <div className="col-span-1">{t('common.tax_rate')}</div>
                        <div className="col-span-3">
                            {t('invoices.line_total')}
                        </div>
                    </div>

                    <DndContext
                        sensors={sensors}
                        collisionDetection={closestCenter}
                        onDragEnd={handleDragEnd}
                    >
                        <SortableContext
                            items={form.data.items.map((i) => i.sort_order)}
                            strategy={verticalListSortingStrategy}
                        >
                            {form.data.items.map((item, index) => (
                                <SortableItem
                                    key={item.sort_order}
                                    item={item}
                                    index={index}
                                    onUpdate={updateItem}
                                    onRemove={removeItem}
                                    t={t}
                                    currency={form.data.currency}
                                    locale={locale}
                                />
                            ))}
                        </SortableContext>
                    </DndContext>

                    <InputError message={form.errors.items} />

                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={addItem}
                        className="w-full"
                    >
                        <Plus className="mr-2 h-4 w-4" />
                        {t('invoices.add_item')}
                    </Button>
                </CardContent>
            </Card>

            <Card>
                <CardContent className="grid gap-4 pt-6 sm:grid-cols-2">
                    <div className="space-y-2">
                        <Label>{t('common.notes')}</Label>
                        <Textarea
                            value={form.data.notes}
                            onChange={(e) =>
                                form.setData('notes', e.target.value)
                            }
                            rows={3}
                        />
                    </div>
                    <div className="space-y-2">
                        <Label>{t('invoices.footer')}</Label>
                        <Textarea
                            value={form.data.footer}
                            onChange={(e) =>
                                form.setData('footer', e.target.value)
                            }
                            rows={3}
                        />
                    </div>
                </CardContent>
            </Card>

            <div className="flex justify-end gap-3">
                <Button
                    type="button"
                    variant="outline"
                    onClick={() => window.history.back()}
                >
                    {t('common.cancel')}
                </Button>
                <Button type="submit" disabled={form.processing}>
                    {submitLabel}
                </Button>
            </div>
        </form>
    );
}
