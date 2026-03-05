<?php

use App\Enums\InvoiceStatus;
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
        'invoice_prefix' => 'INV',
    ]);
    $this->organization->users()->attach($this->user->id, ['role' => OrganizationRole::Owner->value]);
    $this->client = Client::factory()->create(['organization_id' => $this->organization->id]);

    $this->otherUser = User::factory()->create();
    $this->otherOrg = Organization::factory()->create(['owner_id' => $this->otherUser->id]);
    $this->otherOrg->users()->attach($this->otherUser->id, ['role' => OrganizationRole::Owner->value]);
    $this->otherClient = Client::factory()->create(['organization_id' => $this->otherOrg->id]);

    $this->actingAs($this->user);
    session(['current_organization_id' => $this->organization->id]);
});

// --- Client Policy ---

it('allows viewing own clients', function () {
    $this->get('/clients')->assertSuccessful();
});

it('denies viewing another orgs client', function () {
    $this->get("/clients/{$this->otherClient->id}")->assertForbidden();
});

it('denies updating another orgs client', function () {
    $this->put("/clients/{$this->otherClient->id}", [
        'name' => 'Hacked',
    ])->assertForbidden();
});

it('denies deleting another orgs client', function () {
    $this->delete("/clients/{$this->otherClient->id}")->assertForbidden();
});

// --- Invoice Policy ---

it('allows viewing own invoices', function () {
    $this->get('/invoices')->assertSuccessful();
});

it('denies viewing another orgs invoice', function () {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->otherOrg->id,
        'client_id' => $this->otherClient->id,
    ]);

    $this->get("/invoices/{$invoice->id}")->assertForbidden();
});

it('denies updating another orgs invoice', function () {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->otherOrg->id,
        'client_id' => $this->otherClient->id,
    ]);

    $this->put("/invoices/{$invoice->id}", [
        'client_id' => $this->otherClient->id,
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'currency' => 'USD',
        'items' => [['description' => 'Test', 'quantity' => 1, 'unit_price' => 100]],
    ])->assertForbidden();
});

it('denies deleting another orgs invoice', function () {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->otherOrg->id,
        'client_id' => $this->otherClient->id,
    ]);

    $this->delete("/invoices/{$invoice->id}")->assertForbidden();
});

it('denies sending another orgs invoice', function () {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->otherOrg->id,
        'client_id' => $this->otherClient->id,
        'status' => InvoiceStatus::Draft,
    ]);

    $this->post("/invoices/{$invoice->id}/send")->assertForbidden();
});

it('denies marking another orgs invoice as paid', function () {
    $invoice = Invoice::factory()->sent()->create([
        'organization_id' => $this->otherOrg->id,
        'client_id' => $this->otherClient->id,
    ]);

    $this->post("/invoices/{$invoice->id}/mark-paid", [
        'amount' => 100,
        'method' => 'bank_transfer',
        'paid_at' => now()->toDateString(),
    ])->assertForbidden();
});

// --- Recurring Invoice Policy ---

it('allows viewing own recurring invoices', function () {
    $this->get('/recurring-invoices')->assertSuccessful();
});

it('denies editing another orgs recurring invoice', function () {
    $recurring = RecurringInvoice::factory()->create([
        'organization_id' => $this->otherOrg->id,
        'client_id' => $this->otherClient->id,
    ]);

    $this->get("/recurring-invoices/{$recurring->id}/edit")->assertForbidden();
});

it('denies deleting another orgs recurring invoice', function () {
    $recurring = RecurringInvoice::factory()->create([
        'organization_id' => $this->otherOrg->id,
        'client_id' => $this->otherClient->id,
    ]);

    $this->delete("/recurring-invoices/{$recurring->id}")->assertForbidden();
});

// --- Unauthenticated access ---

it('redirects unauthenticated users to login', function () {
    auth()->logout();

    $this->get('/clients')->assertRedirect('/login');
    $this->get('/invoices')->assertRedirect('/login');
    $this->get('/recurring-invoices')->assertRedirect('/login');
    $this->get('/dashboard')->assertRedirect('/login');
});
