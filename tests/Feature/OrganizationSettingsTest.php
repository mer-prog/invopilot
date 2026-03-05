<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create([
        'owner_id' => $this->user->id,
        'default_currency' => 'USD',
        'invoice_prefix' => 'INV',
        'default_payment_terms' => 30,
    ]);
    $this->organization->users()->attach($this->user->id, ['role' => OrganizationRole::Owner->value]);

    $this->actingAs($this->user);
    session(['current_organization_id' => $this->organization->id]);
});

it('displays the organization settings page', function () {
    $this->get('/settings/organization')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('settings/organization')
            ->has('organization')
        );
});

it('updates organization settings', function () {
    $this->patch('/settings/organization', [
        'name' => 'Updated Org',
        'address' => '123 Main St',
        'phone' => '03-1234-5678',
        'tax_id' => 'T1234567890',
        'logo_url' => 'https://example.com/logo.png',
        'default_currency' => 'JPY',
        'invoice_prefix' => 'BILL',
        'default_payment_terms' => 60,
        'default_notes' => 'Thank you for your business',
    ])->assertRedirect();

    $this->organization->refresh();

    expect($this->organization->name)->toBe('Updated Org');
    expect($this->organization->address)->toBe('123 Main St');
    expect($this->organization->phone)->toBe('03-1234-5678');
    expect($this->organization->tax_id)->toBe('T1234567890');
    expect($this->organization->default_currency->value)->toBe('JPY');
    expect($this->organization->invoice_prefix)->toBe('BILL');
    expect($this->organization->default_payment_terms)->toBe(60);
    expect($this->organization->default_notes)->toBe('Thank you for your business');
});

it('validates required fields', function () {
    $this->patch('/settings/organization', [
        'name' => '',
        'default_currency' => '',
        'invoice_prefix' => '',
        'default_payment_terms' => '',
    ])->assertSessionHasErrors(['name', 'default_currency', 'invoice_prefix', 'default_payment_terms']);
});

it('requires authentication', function () {
    auth()->logout();

    $this->get('/settings/organization')
        ->assertRedirect('/login');
});
