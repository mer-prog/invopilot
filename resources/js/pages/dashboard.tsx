import { Head, Link, usePage } from '@inertiajs/react';
import {
    AlertTriangle,
    ArrowDownRight,
    ArrowUpRight,
    DollarSign,
    TrendingUp,
    Users,
} from 'lucide-react';
import { Area, AreaChart, CartesianGrid, XAxis, YAxis } from 'recharts';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    type ChartConfig,
    ChartContainer,
    ChartTooltip,
    ChartTooltipContent,
} from '@/components/ui/chart';
import { useTrans } from '@/hooks/use-trans';
import { formatDate, formatMoney } from '@/lib/formatters';
import AppLayout from '@/layouts/app-layout';
import type {
    ActivityLogEntry,
    BreadcrumbItem,
    DashboardStats,
    MonthlyRevenue,
} from '@/types';

type Props = {
    stats: DashboardStats;
    monthlyRevenue: MonthlyRevenue[];
    recentActivity: ActivityLogEntry[];
};

const chartConfig = {
    revenue: {
        label: 'Revenue',
        color: 'var(--chart-1)',
    },
} satisfies ChartConfig;

export default function Dashboard({
    stats,
    monthlyRevenue,
    recentActivity,
}: Props) {
    const { t } = useTrans();
    const { locale } = usePage().props;
    const currentOrg = usePage().props.currentOrganization;
    const currency = currentOrg?.default_currency ?? 'USD';

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('dashboard.title'), href: '/dashboard' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('dashboard.title')} />
            <div className="flex flex-col gap-4 p-4">
                {stats.overdue_count > 0 && (
                    <Alert variant="destructive">
                        <AlertTriangle className="h-4 w-4" />
                        <AlertDescription className="flex items-center justify-between">
                            <span>
                                {t('dashboard.overdue_alert', {
                                    count: stats.overdue_count,
                                })}
                            </span>
                            <Button
                                variant="outline"
                                size="sm"
                                asChild
                                className="ml-4"
                            >
                                <Link href="/invoices?status=overdue">
                                    {t('dashboard.view_overdue')}
                                </Link>
                            </Button>
                        </AlertDescription>
                    </Alert>
                )}

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('dashboard.this_month_revenue')}
                            </CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {formatMoney(
                                    stats.this_month_revenue,
                                    currency,
                                    locale,
                                )}
                            </div>
                            <p className="mt-1 flex items-center text-xs text-muted-foreground">
                                {stats.growth_rate >= 0 ? (
                                    <ArrowUpRight className="mr-1 h-3 w-3 text-green-500" />
                                ) : (
                                    <ArrowDownRight className="mr-1 h-3 w-3 text-red-500" />
                                )}
                                <span
                                    className={
                                        stats.growth_rate >= 0
                                            ? 'text-green-500'
                                            : 'text-red-500'
                                    }
                                >
                                    {stats.growth_rate > 0 ? '+' : ''}
                                    {stats.growth_rate}%
                                </span>
                                <span className="ml-1">
                                    {t('dashboard.vs_last_month')}
                                </span>
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('dashboard.outstanding')}
                            </CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {formatMoney(
                                    stats.outstanding,
                                    currency,
                                    locale,
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('dashboard.overdue')}
                            </CardTitle>
                            <AlertTriangle className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {stats.overdue_count}
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                {t('dashboard.overdue_count', {
                                    count: stats.overdue_count,
                                })}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('dashboard.total_clients')}
                            </CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {stats.total_clients}
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                {t('dashboard.new_clients', {
                                    count: stats.new_clients,
                                })}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="lg:col-span-2">
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle className="text-base">
                                {t('dashboard.monthly_revenue')}
                            </CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <ChartContainer
                                config={chartConfig}
                                className="h-[300px] w-full"
                            >
                                <AreaChart
                                    data={monthlyRevenue}
                                    margin={{
                                        top: 10,
                                        right: 10,
                                        left: 0,
                                        bottom: 0,
                                    }}
                                >
                                    <CartesianGrid
                                        strokeDasharray="3 3"
                                        vertical={false}
                                    />
                                    <XAxis
                                        dataKey="month"
                                        tickLine={false}
                                        axisLine={false}
                                        tickFormatter={(value: string) =>
                                            value.slice(5)
                                        }
                                    />
                                    <YAxis
                                        tickLine={false}
                                        axisLine={false}
                                        tickFormatter={(value: number) =>
                                            formatMoney(
                                                value,
                                                currency,
                                                locale,
                                            )
                                        }
                                        width={80}
                                    />
                                    <ChartTooltip
                                        content={
                                            <ChartTooltipContent
                                                labelKey="month"
                                            />
                                        }
                                    />
                                    <Area
                                        type="monotone"
                                        dataKey="revenue"
                                        stroke="var(--color-revenue)"
                                        fill="var(--color-revenue)"
                                        fillOpacity={0.2}
                                        strokeWidth={2}
                                    />
                                </AreaChart>
                            </ChartContainer>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">
                                {t('dashboard.recent_activity')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {recentActivity.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    {t('dashboard.no_activity')}
                                </p>
                            ) : (
                                <div className="space-y-3">
                                    {recentActivity.map((activity) => (
                                        <div
                                            key={activity.id}
                                            className="border-b pb-3 last:border-0 last:pb-0"
                                        >
                                            <p className="text-sm">
                                                {activity.description}
                                            </p>
                                            <div className="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                                                {activity.user?.name && (
                                                    <span>
                                                        {activity.user.name}
                                                    </span>
                                                )}
                                                <span>
                                                    {formatDate(
                                                        activity.created_at,
                                                        locale,
                                                    )}
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
