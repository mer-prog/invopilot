<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function getStats(int $organizationId): array
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        $thisMonthRevenue = (float) Invoice::query()
            ->forOrganization($organizationId)
            ->where('status', InvoiceStatus::Paid)
            ->whereBetween('paid_at', [$startOfMonth, $now])
            ->sum('total');

        $lastMonthRevenue = (float) Invoice::query()
            ->forOrganization($organizationId)
            ->where('status', InvoiceStatus::Paid)
            ->whereBetween('paid_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total');

        $growthRate = $lastMonthRevenue > 0
            ? round(($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue * 100, 1)
            : ($thisMonthRevenue > 0 ? 100.0 : 0.0);

        $outstanding = (float) Invoice::query()
            ->forOrganization($organizationId)
            ->whereIn('status', [InvoiceStatus::Sent, InvoiceStatus::Overdue])
            ->sum('total');

        $overdueCount = Invoice::query()
            ->forOrganization($organizationId)
            ->where('status', InvoiceStatus::Overdue)
            ->count();

        $newClientsCount = Client::query()
            ->forOrganization($organizationId)
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        $totalClients = Client::query()
            ->forOrganization($organizationId)
            ->count();

        return [
            'this_month_revenue' => $thisMonthRevenue,
            'last_month_revenue' => $lastMonthRevenue,
            'growth_rate' => $growthRate,
            'outstanding' => $outstanding,
            'overdue_count' => $overdueCount,
            'new_clients' => $newClientsCount,
            'total_clients' => $totalClients,
        ];
    }

    /**
     * @return list<array{month: string, revenue: float}>
     */
    public function getMonthlyRevenue(int $organizationId): array
    {
        $data = [];
        $now = now();

        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $revenue = (float) Invoice::query()
                ->forOrganization($organizationId)
                ->where('status', InvoiceStatus::Paid)
                ->whereBetween('paid_at', [$start, $end])
                ->sum('total');

            $data[] = [
                'month' => $date->format('Y-m'),
                'revenue' => $revenue,
            ];
        }

        return $data;
    }

    /**
     * @return Collection<int, ActivityLog>
     */
    public function getRecentActivity(int $organizationId, int $limit = 10): Collection
    {
        return ActivityLog::query()
            ->forOrganization($organizationId)
            ->with('user:id,name')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
