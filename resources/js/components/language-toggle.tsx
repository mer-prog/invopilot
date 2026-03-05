import { router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

export function LanguageToggle() {
    const { locale } = usePage().props;

    const toggleLocale = () => {
        const newLocale = locale === 'ja' ? 'en' : 'ja';
        router.post(
            '/locale',
            { locale: newLocale },
            { preserveState: true, preserveScroll: true },
        );
    };

    return (
        <Button
            variant="ghost"
            size="icon"
            className="h-9 w-9 cursor-pointer text-base"
            onClick={toggleLocale}
            title={locale === 'ja' ? 'Switch to English' : '日本語に切り替え'}
        >
            {locale === 'ja' ? '🇺🇸' : '🇯🇵'}
        </Button>
    );
}
