import { usePage } from '@inertiajs/react';
import { useCallback } from 'react';

export function useTrans() {
    const { translations } = usePage().props;

    const t = useCallback(
        (key: string, replacements?: Record<string, string | number>): string => {
            const [group, ...rest] = key.split('.');
            const translationKey = rest.join('.');

            const value = translations?.[group]?.[translationKey] ?? key;

            if (!replacements) {
                return value;
            }

            return Object.entries(replacements).reduce<string>(
                (result, [placeholder, replacement]) =>
                    result.replace(`:${placeholder}`, String(replacement)),
                value,
            );
        },
        [translations],
    );

    return { t };
}
