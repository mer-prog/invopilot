<?php

use App\Enums\InvoiceStatus;
use App\Enums\OrganizationRole;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Organization;
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

it('displays the invoices index page', function () {
    Invoice::factory()->count(3)->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->get('/invoices')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('invoices/index')
            ->has('invoices.data', 3)
        );
});

it('filters invoices by status', function () {
    Invoice::factory()->draft()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);
    Invoice::factory()->paid()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->get('/invoices?status=draft')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('invoices/index')
            ->has('invoices.data', 1)
        );
});

it('displays the create invoice page', function () {
    $this->get('/invoices/create')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('invoices/create')
            ->has('clients')
            ->has('defaultCurrency')
        );
});

it('creates an invoice with items', function () {
    $this->post('/invoices', [
        'client_id' => $this->client->id,
        'issue_date' => '2026-03-05',
        'due_date' => '2026-04-04',
        'currency' => 'USD',
        'discount_amount' => 0,
        'notes' => '',
        'footer' => '',
        'items' => [
            [
                'description' => 'Web Development',
                'quantity' => 10,
                'unit_price' => 150,
                'tax_rate' => 10,
            ],
            [
                'description' => 'Design Work',
                'quantity' => 5,
                'unit_price' => 100,
                'tax_rate' => 10,
            ],
        ],
    ])->assertRedirect();

    $invoice = Invoice::query()->where('organization_id', $this->organization->id)->latest('id')->first();

    expect($invoice)->not->toBeNull();
    expect($invoice->status)->toBe(InvoiceStatus::Draft);
    expect($invoice->items)->toHaveCount(2);
    expect((float) $invoice->subtotal)->toBe(2000.00);
    expect((float) $invoice->tax_amount)->toBe(200.00);
    expect((float) $invoice->total)->toBe(2200.00);
});

it('validates invoice items are required', function () {
    $this->post('/invoices', [
        'client_id' => $this->client->id,
        'issue_date' => '2026-03-05',
        'due_date' => '2026-04-04',
        'currency' => 'USD',
        'items' => [],
    ])->assertSessionHasErrors(['items']);
});

it('displays an invoice', function () {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->get("/invoices/{$invoice->id}")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('invoices/show')
            ->where('invoice.id', $invoice->id)
        );
});

it('sends a draft invoice', function () {
    $invoice = Invoice::factory()->draft()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->post("/invoices/{$invoice->id}/send")
        ->assertRedirect();

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::Sent);
    expect($invoice->sent_at)->not->toBeNull();
});

it('marks an invoice as paid', function () {
    $invoice = Invoice::factory()->sent()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->post("/invoices/{$invoice->id}/mark-paid")
        ->assertRedirect();

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::Paid);
    expect($invoice->paid_at)->not->toBeNull();
});

it('duplicates an invoice', function () {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->post("/invoices/{$invoice->id}/duplicate")
        ->assertRedirect();

    expect(Invoice::query()->where('organization_id', $this->organization->id)->count())->toBe(2);
});

it('deletes an invoice', function () {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->delete("/invoices/{$invoice->id}")
        ->assertRedirect('/invoices');

    $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
});

it('prevents access to invoices from other organizations', function () {
    $otherOrg = Organization::factory()->create();
    $invoice = Invoice::factory()->create(['organization_id' => $otherOrg->id]);

    $this->get("/invoices/{$invoice->id}")
        ->assertForbidden();
});
