import type { ReactNode } from 'react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

export type Column<T> = {
    key: string;
    label: string;
    className?: string;
    render: (item: T) => ReactNode;
};

type Props<T> = {
    columns: Column<T>[];
    data: T[];
    keyExtractor: (item: T) => string | number;
    onRowClick?: (item: T) => void;
    emptyContent?: ReactNode;
};

export function DataTable<T>({
    columns,
    data,
    keyExtractor,
    onRowClick,
    emptyContent,
}: Props<T>) {
    if (data.length === 0 && emptyContent) {
        return <>{emptyContent}</>;
    }

    return (
        <div className="overflow-x-auto rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        {columns.map((col) => (
                            <TableHead key={col.key} className={col.className}>
                                {col.label}
                            </TableHead>
                        ))}
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {data.map((item) => (
                        <TableRow
                            key={keyExtractor(item)}
                            className={
                                onRowClick
                                    ? 'cursor-pointer hover:bg-muted/50'
                                    : undefined
                            }
                            onClick={() => onRowClick?.(item)}
                        >
                            {columns.map((col) => (
                                <TableCell
                                    key={col.key}
                                    className={col.className}
                                >
                                    {col.render(item)}
                                </TableCell>
                            ))}
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </div>
    );
}
