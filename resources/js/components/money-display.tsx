import { usePage } from '@inertiajs/react';
import { formatMoney } from '@/lib/formatters';

type Props = {
    amount: number | string;
    currency?: string;
    className?: string;
};

export function MoneyDisplay({ amount, currency, className }: Props) {
    const { locale, currentOrganization } = usePage().props;
    const displayCurrency =
        currency ?? currentOrganization?.default_currency ?? 'USD';

    return (
        <span className={className}>
            {formatMoney(amount, displayCurrency, locale)}
        </span>
    );
}
