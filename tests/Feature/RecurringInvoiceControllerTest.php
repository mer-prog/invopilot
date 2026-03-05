<?php

use App\Enums\OrganizationRole;
use App\Models\Client;
use App\Models\Organization;
use App\Models\RecurringInvoice;
use App\Models\User;

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

it('displays the recurring invoices index page', function () {
    RecurringInvoice::factory()->count(3)->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->get('/recurring-invoices')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('recurring-invoices/index')
            ->has('recurringInvoices.data', 3)
        );
});

it('displays the create recurring invoice page', function () {
    $this->get('/recurring-invoices/create')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('recurring-invoices/create')
            ->has('clients')
        );
});

it('creates a recurring invoice', function () {
    $this->post('/recurring-invoices', [
        'client_id' => $this->client->id,
        'frequency' => 'monthly',
        'next_issue_date' => '2026-04-01',
        'tax_rate' => 10,
        'items' => [
            ['description' => 'Monthly Retainer', 'quantity' => 1, 'unit_price' => 5000],
        ],
    ])->assertRedirect('/recurring-invoices');

    $this->assertDatabaseHas('recurring_invoices', [
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'frequency' => 'monthly',
        'is_active' => true,
    ]);
});

it('updates a recurring invoice', function () {
    $recurring = RecurringInvoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->put("/recurring-invoices/{$recurring->id}", [
        'client_id' => $this->client->id,
        'frequency' => 'quarterly',
        'next_issue_date' => '2026-04-01',
        'tax_rate' => 8,
        'items' => [
            ['description' => 'Updated Service', 'quantity' => 2, 'unit_price' => 3000],
        ],
    ])->assertRedirect('/recurring-invoices');

    $this->assertDatabaseHas('recurring_invoices', [
        'id' => $recurring->id,
        'frequency' => 'quarterly',
    ]);
});

it('deletes a recurring invoice', function () {
    $recurring = RecurringInvoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->delete("/recurring-invoices/{$recurring->id}")
        ->assertRedirect('/recurring-invoices');

    $this->assertDatabaseMissing('recurring_invoices', ['id' => $recurring->id]);
});

it('toggles active status', function () {
    $recurring = RecurringInvoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'is_active' => true,
    ]);

    $this->post("/recurring-invoices/{$recurring->id}/toggle")
        ->assertRedirect();

    $recurring->refresh();
    expect($recurring->is_active)->toBeFalse();

    $this->post("/recurring-invoices/{$recurring->id}/toggle")
        ->assertRedirect();

    $recurring->refresh();
    expect($recurring->is_active)->toBeTrue();
});

it('prevents access to recurring invoices from other organizations', function () {
    $otherOrg = Organization::factory()->create();
    $recurring = RecurringInvoice::factory()->create(['organization_id' => $otherOrg->id]);

    $this->get("/recurring-invoices/{$recurring->id}/edit")
        ->assertForbidden();
});
