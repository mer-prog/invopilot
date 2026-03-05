import { Transition } from '@headlessui/react';
import { Form, Head } from '@inertiajs/react';
import OrganizationController from '@/actions/App/Http/Controllers/Settings/OrganizationController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { useTrans } from '@/hooks/use-trans';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit } from '@/routes/organization';
import type { BreadcrumbItem } from '@/types';

type Organization = {
    id: number;
    name: string;
    address: string | null;
    phone: string | null;
    tax_id: string | null;
    logo_url: string | null;
    default_currency: string;
    invoice_prefix: string;
    default_payment_terms: number;
    default_notes: string | null;
};

type Props = {
    organization: Organization;
};

const currencies = [
    'USD',
    'EUR',
    'GBP',
    'JPY',
    'CAD',
    'AUD',
    'CHF',
    'CNY',
    'KRW',
    'TWD',
];

export default function OrganizationSettings({ organization }: Props) {
    const { t } = useTrans();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('settings.organization'),
            href: edit(),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('settings.organization')} />

            <h1 className="sr-only">{t('settings.organization')}</h1>

            <SettingsLayout>
                <Form
                    {...OrganizationController.update.form()}
                    options={{
                        preserveScroll: true,
                    }}
                    className="space-y-6"
                >
                    {({ processing, recentlySuccessful, errors }) => (
                        <>
                            <div className="space-y-6">
                                <Heading
                                    variant="small"
                                    title={t('settings.organization_info')}
                                    description={t(
                                        'settings.organization_info_description',
                                    )}
                                />

                                <div className="grid gap-2">
                                    <Label htmlFor="name">
                                        {t('settings.organization_name')}
                                    </Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        defaultValue={organization.name}
                                        required
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="address">
                                        {t('settings.address')}
                                    </Label>
                                    <Textarea
                                        id="address"
                                        name="address"
                                        defaultValue={
                                            organization.address ?? ''
                                        }
                                        rows={3}
                                    />
                                    <InputError message={errors.address} />
                                </div>

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="phone">
                                            {t('settings.phone')}
                                        </Label>
                                        <Input
                                            id="phone"
                                            name="phone"
                                            defaultValue={
                                                organization.phone ?? ''
                                            }
                                        />
                                        <InputError message={errors.phone} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="tax_id">
                                            {t('settings.tax_id')}
                                        </Label>
                                        <Input
                                            id="tax_id"
                                            name="tax_id"
                                            defaultValue={
                                                organization.tax_id ?? ''
                                            }
                                        />
                                        <InputError message={errors.tax_id} />
                                    </div>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="logo_url">
                                        {t('settings.logo_url')}
                                    </Label>
                                    <Input
                                        id="logo_url"
                                        name="logo_url"
                                        type="url"
                                        defaultValue={
                                            organization.logo_url ?? ''
                                        }
                                    />
                                    <InputError message={errors.logo_url} />
                                </div>
                            </div>

                            <Separator />

                            <div className="space-y-6">
                                <Heading
                                    variant="small"
                                    title={t('settings.invoice_defaults')}
                                    description={t(
                                        'settings.invoice_defaults_description',
                                    )}
                                />

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="default_currency">
                                            {t('settings.default_currency')}
                                        </Label>
                                        <Select
                                            name="default_currency"
                                            defaultValue={
                                                organization.default_currency
                                            }
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {currencies.map((c) => (
                                                    <SelectItem
                                                        key={c}
                                                        value={c}
                                                    >
                                                        {c}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError
                                            message={errors.default_currency}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="invoice_prefix">
                                            {t('settings.invoice_prefix')}
                                        </Label>
                                        <Input
                                            id="invoice_prefix"
                                            name="invoice_prefix"
                                            defaultValue={
                                                organization.invoice_prefix
                                            }
                                            required
                                        />
                                        <InputError
                                            message={errors.invoice_prefix}
                                        />
                                    </div>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="default_payment_terms">
                                        {t('settings.default_payment_terms')}
                                    </Label>
                                    <Input
                                        id="default_payment_terms"
                                        name="default_payment_terms"
                                        type="number"
                                        min={1}
                                        max={365}
                                        defaultValue={
                                            organization.default_payment_terms
                                        }
                                        required
                                    />
                                    <InputError
                                        message={errors.default_payment_terms}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="default_notes">
                                        {t('settings.default_notes')}
                                    </Label>
                                    <Textarea
                                        id="default_notes"
                                        name="default_notes"
                                        defaultValue={
                                            organization.default_notes ?? ''
                                        }
                                        rows={4}
                                    />
                                    <InputError
                                        message={errors.default_notes}
                                    />
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>
                                    {t('settings.save')}
                                </Button>

                                <Transition
                                    show={recentlySuccessful}
                                    enter="transition ease-in-out"
                                    enterFrom="opacity-0"
                                    leave="transition ease-in-out"
                                    leaveTo="opacity-0"
                                >
                                    <p className="text-sm text-neutral-600">
                                        {t('settings.saved')}
                                    </p>
                                </Transition>
                            </div>
                        </>
                    )}
                </Form>
            </SettingsLayout>
        </AppLayout>
    );
}
