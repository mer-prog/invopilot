<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index(): Response
    {
        $orgId = (int) session('current_organization_id');

        return Inertia::render('dashboard', [
            'stats' => $this->dashboardService->getStats($orgId),
            'monthlyRevenue' => $this->dashboardService->getMonthlyRevenue($orgId),
            'recentActivity' => $this->dashboardService->getRecentActivity($orgId),
        ]);
    }
}
