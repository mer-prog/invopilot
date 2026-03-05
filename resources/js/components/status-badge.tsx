import { Badge } from '@/components/ui/badge';
import { useTrans } from '@/hooks/use-trans';
import { cn } from '@/lib/utils';
import type { InvoiceStatus } from '@/types';

const statusStyles: Record<InvoiceStatus, string> = {
    draft: 'bg-neutral-100 text-neutral-700 border-neutral-200 dark:bg-neutral-800 dark:text-neutral-300 dark:border-neutral-700',
    sent: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800',
    paid: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800',
    overdue: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800',
    cancelled: 'bg-neutral-50 text-neutral-500 border-neutral-200 dark:bg-neutral-800/50 dark:text-neutral-400 dark:border-neutral-700',
};

const translationKeys: Record<InvoiceStatus, string> = {
    draft: 'invoices.status_draft',
    sent: 'invoices.status_sent',
    paid: 'invoices.status_paid',
    overdue: 'invoices.status_overdue',
    cancelled: 'invoices.status_cancelled',
};

export function StatusBadge({ status }: { status: InvoiceStatus }) {
    const { t } = useTrans();

    return (
        <Badge
            variant="outline"
            className={cn('font-medium', statusStyles[status])}
        >
            {t(translationKeys[status])}
        </Badge>
    );
}
