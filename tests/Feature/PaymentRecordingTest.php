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

it('records a payment for an invoice', function () {
    $invoice = Invoice::factory()->sent()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'total' => 1000,
    ]);

    $this->post("/invoices/{$invoice->id}/mark-paid", [
        'amount' => 500,
        'method' => 'bank_transfer',
        'paid_at' => '2026-03-05',
        'reference' => 'REF-001',
    ])->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'invoice_id' => $invoice->id,
        'amount' => 500,
        'method' => 'bank_transfer',
        'reference' => 'REF-001',
    ]);
});

it('marks invoice as paid when fully paid', function () {
    $invoice = Invoice::factory()->sent()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'total' => 1000,
    ]);

    $this->post("/invoices/{$invoice->id}/mark-paid", [
        'amount' => 1000,
        'method' => 'credit_card',
        'paid_at' => '2026-03-05',
    ])->assertRedirect();

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::Paid);
    expect($invoice->paid_at)->not->toBeNull();
});

it('keeps invoice as sent for partial payments', function () {
    $invoice = Invoice::factory()->sent()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'total' => 1000,
    ]);

    $this->post("/invoices/{$invoice->id}/mark-paid", [
        'amount' => 500,
        'method' => 'bank_transfer',
        'paid_at' => '2026-03-05',
    ])->assertRedirect();

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::Sent);
    expect($invoice->paid_at)->toBeNull();
});

it('validates payment amount is required', function () {
    $invoice = Invoice::factory()->sent()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->post("/invoices/{$invoice->id}/mark-paid", [
        'method' => 'bank_transfer',
        'paid_at' => '2026-03-05',
    ])->assertSessionHasErrors(['amount']);
});

it('prevents recording payment for draft invoices', function () {
    $invoice = Invoice::factory()->draft()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->post("/invoices/{$invoice->id}/mark-paid", [
        'amount' => 100,
        'method' => 'cash',
        'paid_at' => '2026-03-05',
    ])->assertForbidden();
});
