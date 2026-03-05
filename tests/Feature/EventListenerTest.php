<?php

use App\Enums\InvoiceStatus;
use App\Enums\OrganizationRole;
use App\Enums\PaymentMethod;
use App\Events\InvoiceCreated;
use App\Events\InvoiceOverdue;
use App\Events\InvoiceSent;
use App\Events\PaymentRecorded;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Payment;
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

it('logs activity when an invoice is created', function () {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    InvoiceCreated::dispatch($invoice);

    $this->assertDatabaseHas('activity_logs', [
        'organization_id' => $this->organization->id,
        'action' => 'created',
        'subject_type' => Invoice::class,
        'subject_id' => $invoice->id,
    ]);
});

it('logs activity when an invoice is sent', function () {
    $invoice = Invoice::factory()->sent()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    InvoiceSent::dispatch($invoice);

    $this->assertDatabaseHas('activity_logs', [
        'organization_id' => $this->organization->id,
        'action' => 'sent',
        'subject_type' => Invoice::class,
        'subject_id' => $invoice->id,
    ]);
});

it('logs activity when a payment is recorded', function () {
    $invoice = Invoice::factory()->sent()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'total' => 1000,
    ]);
    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 500,
        'method' => PaymentMethod::BankTransfer,
    ]);

    PaymentRecorded::dispatch($payment, $invoice);

    $this->assertDatabaseHas('activity_logs', [
        'organization_id' => $this->organization->id,
        'action' => 'payment_recorded',
        'subject_type' => Invoice::class,
        'subject_id' => $invoice->id,
    ]);
});

it('marks invoice as paid when total payments cover the total', function () {
    $invoice = Invoice::factory()->sent()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'total' => 1000,
    ]);
    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 1000,
        'method' => PaymentMethod::BankTransfer,
    ]);

    PaymentRecorded::dispatch($payment, $invoice);

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::Paid);
    expect($invoice->paid_at)->not->toBeNull();
});

it('does not mark invoice as paid for partial payment', function () {
    $invoice = Invoice::factory()->sent()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'total' => 1000,
    ]);
    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 500,
        'method' => PaymentMethod::BankTransfer,
    ]);

    PaymentRecorded::dispatch($payment, $invoice);

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::Sent);
    expect($invoice->paid_at)->toBeNull();
});

it('logs activity when an invoice becomes overdue', function () {
    $invoice = Invoice::factory()->sent()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    InvoiceOverdue::dispatch($invoice);

    $this->assertDatabaseHas('activity_logs', [
        'organization_id' => $this->organization->id,
        'action' => 'overdue',
        'subject_type' => Invoice::class,
        'subject_id' => $invoice->id,
    ]);
});
