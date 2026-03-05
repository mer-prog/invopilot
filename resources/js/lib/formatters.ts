const moneyFormatters = new Map<string, Intl.NumberFormat>();
const dateFormatters = new Map<string, Intl.DateTimeFormat>();

export function formatMoney(
    amount: number | string,
    currency: string = 'USD',
    locale: string = 'en',
): string {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    const key = `${locale}-${currency}`;

    if (!moneyFormatters.has(key)) {
        moneyFormatters.set(
            key,
            new Intl.NumberFormat(locale === 'ja' ? 'ja-JP' : 'en-US', {
                style: 'currency',
                currency,
                minimumFractionDigits: currency === 'JPY' ? 0 : 2,
                maximumFractionDigits: currency === 'JPY' ? 0 : 2,
            }),
        );
    }

    return moneyFormatters.get(key)!.format(num);
}

export function formatDate(
    date: string | Date,
    locale: string = 'en',
    options?: Intl.DateTimeFormatOptions,
): string {
    const d = typeof date === 'string' ? new Date(date) : date;
    const formatLocale = locale === 'ja' ? 'ja-JP' : 'en-US';
    const defaultOptions: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    };
    const opts = options ?? defaultOptions;
    const key = `${formatLocale}-${JSON.stringify(opts)}`;

    if (!dateFormatters.has(key)) {
        dateFormatters.set(key, new Intl.DateTimeFormat(formatLocale, opts));
    }

    return dateFormatters.get(key)!.format(d);
}
