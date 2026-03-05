<?php

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
        'invoice_prefix' => 'INV',
    ]);
    $this->organization->users()->attach($this->user->id, ['role' => OrganizationRole::Owner->value]);
    $this->client = Client::factory()->create(['organization_id' => $this->organization->id]);

    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

it('requires authentication', function () {
    $this->getJson('/api/invoices')
        ->assertUnauthorized();
});

it('lists invoices via api', function () {
    Invoice::factory()->count(3)->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->withToken($this->token)
        ->getJson('/api/invoices')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('shows an invoice via api', function () {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->withToken($this->token)
        ->getJson("/api/invoices/{$invoice->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $invoice->id);
});

it('prevents access to another organizations invoice', function () {
    $otherOrg = Organization::factory()->create();
    $otherClient = Client::factory()->create(['organization_id' => $otherOrg->id]);
    $invoice = Invoice::factory()->create([
        'organization_id' => $otherOrg->id,
        'client_id' => $otherClient->id,
    ]);

    $this->withToken($this->token)
        ->getJson("/api/invoices/{$invoice->id}")
        ->assertForbidden();
});

it('deletes an invoice via api', function () {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->withToken($this->token)
        ->deleteJson("/api/invoices/{$invoice->id}")
        ->assertNoContent();

    expect(Invoice::query()->find($invoice->id))->toBeNull();
});
