<?php

use App\Enums\OrganizationRole;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\RecurringInvoice;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create([
        'owner_id' => $this->user->id,
        'default_currency' => 'USD',
        'default_payment_terms' => 30,
    ]);
    $this->organization->users()->attach($this->user->id, ['role' => OrganizationRole::Owner->value]);
    $this->client = Client::factory()->create(['organization_id' => $this->organization->id]);
});

it('generates invoices for due recurring templates', function () {
    RecurringInvoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'frequency' => 'monthly',
        'next_issue_date' => now()->subDay(),
        'is_active' => true,
        'items' => [
            ['description' => 'Monthly Service', 'quantity' => 1, 'unit_price' => 1000],
        ],
        'tax_rate' => 10,
    ]);

    $this->artisan('invoices:process-recurring')->assertSuccessful();

    expect(Invoice::query()->where('organization_id', $this->organization->id)->count())->toBe(1);
});

it('updates next_issue_date after generation', function () {
    $recurring = RecurringInvoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'frequency' => 'monthly',
        'next_issue_date' => now()->subDay(),
        'is_active' => true,
        'items' => [
            ['description' => 'Service', 'quantity' => 1, 'unit_price' => 500],
        ],
    ]);

    $this->artisan('invoices:process-recurring')->assertSuccessful();

    $recurring->refresh();
    expect($recurring->next_issue_date->isFuture())->toBeTrue();
    expect($recurring->last_generated_at)->not->toBeNull();
});

it('skips inactive recurring invoices', function () {
    RecurringInvoice::factory()->inactive()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'next_issue_date' => now()->subDay(),
        'items' => [
            ['description' => 'Service', 'quantity' => 1, 'unit_price' => 500],
        ],
    ]);

    $this->artisan('invoices:process-recurring')->assertSuccessful();

    expect(Invoice::query()->where('organization_id', $this->organization->id)->count())->toBe(0);
});

it('deactivates recurring invoice past end_date', function () {
    $recurring = RecurringInvoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'frequency' => 'monthly',
        'next_issue_date' => now()->subDay(),
        'end_date' => now(),
        'is_active' => true,
        'items' => [
            ['description' => 'Service', 'quantity' => 1, 'unit_price' => 500],
        ],
    ]);

    $this->artisan('invoices:process-recurring')->assertSuccessful();

    $recurring->refresh();
    expect($recurring->is_active)->toBeFalse();
});
