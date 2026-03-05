<?php

use App\Enums\OrganizationRole;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\InvoiceSentNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create([
        'owner_id' => $this->user->id,
        'default_currency' => 'USD',
    ]);
    $this->organization->users()->attach($this->user->id, ['role' => OrganizationRole::Owner->value]);
    $this->client = Client::factory()->create([
        'organization_id' => $this->organization->id,
        'email' => 'client@example.com',
    ]);

    $this->actingAs($this->user);
    session(['current_organization_id' => $this->organization->id]);
});

it('sends invoice email when sending an invoice', function () {
    Notification::fake();

    $invoice = Invoice::factory()->draft()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $this->post("/invoices/{$invoice->id}/send")
        ->assertRedirect();

    Notification::assertSentTo($this->client, InvoiceSentNotification::class);
});

it('does not send email when client has no email', function () {
    Notification::fake();

    $clientNoEmail = Client::factory()->create([
        'organization_id' => $this->organization->id,
        'email' => null,
    ]);
    $invoice = Invoice::factory()->draft()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $clientNoEmail->id,
    ]);

    $this->post("/invoices/{$invoice->id}/send")
        ->assertRedirect();

    Notification::assertNothingSent();
});
