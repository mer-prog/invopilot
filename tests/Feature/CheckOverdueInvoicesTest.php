<?php

use App\Enums\InvoiceStatus;
use App\Enums\OrganizationRole;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\OverdueReminderNotification;
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
});

it('marks sent invoices past due date as overdue', function () {
    $invoice = Invoice::factory()->sent()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'due_date' => now()->subDays(5),
    ]);

    Notification::fake();

    $this->artisan('invoices:check-overdue')->assertSuccessful();

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::Overdue);
});

it('does not mark invoices that are not past due', function () {
    $invoice = Invoice::factory()->sent()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'due_date' => now()->addDays(5),
    ]);

    $this->artisan('invoices:check-overdue')->assertSuccessful();

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::Sent);
});

it('does not mark draft invoices as overdue', function () {
    $invoice = Invoice::factory()->draft()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'due_date' => now()->subDays(5),
    ]);

    $this->artisan('invoices:check-overdue')->assertSuccessful();

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::Draft);
});

it('sends overdue reminder notification', function () {
    Notification::fake();

    Invoice::factory()->sent()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'due_date' => now()->subDays(3),
    ]);

    $this->artisan('invoices:check-overdue')->assertSuccessful();

    Notification::assertSentTo($this->client, OverdueReminderNotification::class);
});
