<?php

use App\Enums\OrganizationRole;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
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

it('downloads an invoice PDF', function () {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);
    InvoiceItem::factory()->count(2)->create(['invoice_id' => $invoice->id]);

    $this->get("/invoices/{$invoice->id}/pdf")
        ->assertSuccessful()
        ->assertHeader('content-type', 'application/pdf');
});

it('prevents PDF download for other organizations', function () {
    $otherOrg = Organization::factory()->create();
    $invoice = Invoice::factory()->create(['organization_id' => $otherOrg->id]);

    $this->get("/invoices/{$invoice->id}/pdf")
        ->assertForbidden();
});
