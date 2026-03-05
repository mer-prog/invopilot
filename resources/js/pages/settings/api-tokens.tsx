import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Copy, Key, Trash2 } from 'lucide-react';
import { type FormEvent, useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
import SettingsLayout from '@/layouts/settings/layout';
import { index } from '@/routes/api-tokens';
import type { BreadcrumbItem } from '@/types';

type Token = {
    id: number;
    name: string;
    last_used_at: string | null;
    created_at: string;
};

type Props = {
    tokens: Token[];
};

export default function ApiTokens({ tokens }: Props) {
    const { t } = useTrans();
    const { locale } = usePage().props;
    const flash = usePage().props.flash as { success?: string } | undefined;
    const [copied, setCopied] = useState(false);

    const newToken = flash?.success?.includes('|') ? flash.success : null;

    const form = useForm({
        name: '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('settings.api_tokens'),
            href: index(),
        },
    ];

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        form.post(index.url(), {
            preserveScroll: true,
            onSuccess: () => form.reset('name'),
        });
    }

    function handleCopy() {
        if (newToken) {
            navigator.clipboard.writeText(newToken);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        }
    }

    function handleRevoke(tokenId: number) {
        if (confirm(t('settings.revoke_confirm'))) {
            router.delete(`/settings/api-tokens/${tokenId}`, {
                preserveScroll: true,
            });
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('settings.api_tokens')} />

            <h1 className="sr-only">{t('settings.api_tokens')}</h1>

            <SettingsLayout>
                <div className="space-y-6">
                    <Heading
                        variant="small"
                        title={t('settings.api_tokens')}
                        description={t('settings.api_tokens_description')}
                    />

                    {newToken && (
                        <Alert>
                            <Key className="h-4 w-4" />
                            <AlertDescription>
                                <p className="mb-2 text-sm">
                                    {t('settings.new_token_message')}
                                </p>
                                <div className="flex items-center gap-2">
                                    <code className="flex-1 rounded bg-muted px-2 py-1 text-xs break-all">
                                        {newToken}
                                    </code>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={handleCopy}
                                    >
                                        <Copy className="h-3 w-3" />
                                        {copied ? 'Copied!' : 'Copy'}
                                    </Button>
                                </div>
                            </AlertDescription>
                        </Alert>
                    )}

                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="grid gap-2">
                            <Label htmlFor="name">
                                {t('settings.token_name')}
                            </Label>
                            <div className="flex gap-2">
                                <Input
                                    id="name"
                                    value={form.data.name}
                                    onChange={(e) =>
                                        form.setData('name', e.target.value)
                                    }
                                    placeholder={t(
                                        'settings.token_name_placeholder',
                                    )}
                                    className="flex-1"
                                />
                                <Button
                                    type="submit"
                                    disabled={form.processing}
                                >
                                    {t('settings.create_token')}
                                </Button>
                            </div>
                            <InputError message={form.errors.name} />
                        </div>
                    </form>

                    {tokens.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            {t('settings.no_tokens')}
                        </p>
                    ) : (
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>
                                            {t('settings.token_name')}
                                        </TableHead>
                                        <TableHead>
                                            {t('settings.last_used')}
                                        </TableHead>
                                        <TableHead>
                                            {t('settings.created_at')}
                                        </TableHead>
                                        <TableHead className="w-[80px]" />
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {tokens.map((token) => (
                                        <TableRow key={token.id}>
                                            <TableCell className="font-medium">
                                                {token.name}
                                            </TableCell>
                                            <TableCell>
                                                {token.last_used_at
                                                    ? formatDate(
                                                          token.last_used_at,
                                                          locale as string,
                                                      )
                                                    : t(
                                                          'settings.never_used',
                                                      )}
                                            </TableCell>
                                            <TableCell>
                                                {formatDate(
                                                    token.created_at,
                                                    locale as string,
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() =>
                                                        handleRevoke(token.id)
                                                    }
                                                >
                                                    <Trash2 className="h-4 w-4 text-destructive" />
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    )}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
