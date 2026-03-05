import type { LucideIcon } from 'lucide-react';
import { InboxIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';

type Props = {
    icon?: LucideIcon;
    title: string;
    description?: string;
    actionLabel?: string;
    actionHref?: string;
    onAction?: () => void;
};

export function EmptyState({
    icon: Icon = InboxIcon,
    title,
    description,
    actionLabel,
    actionHref,
    onAction,
}: Props) {
    return (
        <div className="flex flex-col items-center justify-center rounded-lg border border-dashed p-12 text-center">
            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                <Icon className="h-6 w-6 text-muted-foreground" />
            </div>
            <h3 className="mt-4 text-lg font-semibold">{title}</h3>
            {description && (
                <p className="mt-2 text-sm text-muted-foreground">
                    {description}
                </p>
            )}
            {actionLabel && (actionHref || onAction) && (
                <div className="mt-6">
                    {actionHref ? (
                        <Button asChild>
                            <a href={actionHref}>{actionLabel}</a>
                        </Button>
                    ) : (
                        <Button onClick={onAction}>{actionLabel}</Button>
                    )}
                </div>
            )}
        </div>
    );
}
