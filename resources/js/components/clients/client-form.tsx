import type { InertiaFormProps } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useTrans } from '@/hooks/use-trans';

type ClientFormData = {
    name: string;
    email: string;
    phone: string;
    company: string;
    address: string;
    tax_id: string;
    notes: string;
};

type Props = {
    form: InertiaFormProps<ClientFormData>;
    onSubmit: (e: React.FormEvent) => void;
    submitLabel: string;
};

export type { ClientFormData };

export function ClientForm({ form, onSubmit, submitLabel }: Props) {
    const { t } = useTrans();

    return (
        <form onSubmit={onSubmit} className="space-y-6">
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="name">{t('common.name')} *</Label>
                    <Input
                        id="name"
                        value={form.data.name}
                        onChange={(e) => form.setData('name', e.target.value)}
                        autoFocus
                    />
                    <InputError message={form.errors.name} />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="email">{t('common.email')}</Label>
                    <Input
                        id="email"
                        type="email"
                        value={form.data.email}
                        onChange={(e) => form.setData('email', e.target.value)}
                    />
                    <InputError message={form.errors.email} />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="company">{t('clients.company')}</Label>
                    <Input
                        id="company"
                        value={form.data.company}
                        onChange={(e) =>
                            form.setData('company', e.target.value)
                        }
                    />
                    <InputError message={form.errors.company} />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="phone">{t('common.phone')}</Label>
                    <Input
                        id="phone"
                        value={form.data.phone}
                        onChange={(e) => form.setData('phone', e.target.value)}
                    />
                    <InputError message={form.errors.phone} />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="tax_id">{t('clients.tax_id')}</Label>
                    <Input
                        id="tax_id"
                        value={form.data.tax_id}
                        onChange={(e) => form.setData('tax_id', e.target.value)}
                    />
                    <InputError message={form.errors.tax_id} />
                </div>
            </div>

            <div className="space-y-2">
                <Label htmlFor="address">{t('common.address')}</Label>
                <Textarea
                    id="address"
                    value={form.data.address}
                    onChange={(e) => form.setData('address', e.target.value)}
                    rows={3}
                />
                <InputError message={form.errors.address} />
            </div>

            <div className="space-y-2">
                <Label htmlFor="notes">{t('common.notes')}</Label>
                <Textarea
                    id="notes"
                    value={form.data.notes}
                    onChange={(e) => form.setData('notes', e.target.value)}
                    rows={3}
                />
                <InputError message={form.errors.notes} />
            </div>

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
