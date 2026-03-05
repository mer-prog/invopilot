<?php

use App\Enums\InvoiceStatus;
use App\Enums\OrganizationRole;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Services\DashboardService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create([
        'owner_id' => $this->user->id,
        'default_currency' => 'USD',
    ]);
    $this->organization->users()->attach($this->user->id, ['role' => OrganizationRole::Owner->value]);
    $this->client = Client::factory()->create(['organization_id' => $this->organization->id]);

    $this->actingAs($this->user);
    session(['current_organization_id' => $this->organization->id]);
});

it('displays the dashboard page', function () {
    $this->get('/dashboard')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->has('stats')
            ->has('monthlyRevenue')
            ->has('recentActivity')
        );
});

it('returns correct stats', function () {
    Invoice::factory()->paid()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'total' => 1000,
        'paid_at' => now(),
    ]);
    Invoice::factory()->sent()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'total' => 500,
    ]);
    Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'status' => InvoiceStatus::Overdue,
        'total' => 200,
    ]);

    $service = app(DashboardService::class);
    $stats = $service->getStats($this->organization->id);

    expect($stats['this_month_revenue'])->toBe(1000.0);
    expect($stats['outstanding'])->toBe(700.0);
    expect($stats['overdue_count'])->toBe(1);
});

it('returns 12 months of revenue data', function () {
    $service = app(DashboardService::class);
    $data = $service->getMonthlyRevenue($this->organization->id);

    expect($data)->toHaveCount(12);
    expect($data[0])->toHaveKeys(['month', 'revenue']);
});
