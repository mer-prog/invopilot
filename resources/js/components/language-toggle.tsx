import { router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { useTrans } from '@/hooks/use-trans';

export function LanguageToggle() {
    const { locale } = usePage().props;
    const { t } = useTrans();

    const toggleLocale = () => {
        const newLocale = locale === 'ja' ? 'en' : 'ja';
        router.post(
            '/locale',
            { locale: newLocale },
            { preserveState: true, preserveScroll: true },
        );
    };

    const label = locale === 'ja' ? 'Switch to English' : '日本語に切り替え';

    return (
        <Button
            variant="ghost"
            size="icon"
            className="h-9 w-9 cursor-pointer text-base"
            onClick={toggleLocale}
            title={label}
            aria-label={label}
        >
            {locale === 'ja' ? '🇺🇸' : '🇯🇵'}
        </Button>
    );
}
