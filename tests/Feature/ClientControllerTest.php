<?php

use App\Enums\OrganizationRole;
use App\Models\Client;
use App\Models\Organization;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create(['owner_id' => $this->user->id]);
    $this->organization->users()->attach($this->user->id, ['role' => OrganizationRole::Owner->value]);

    $this->actingAs($this->user);
    session(['current_organization_id' => $this->organization->id]);
});

it('displays the clients index page', function () {
    Client::factory()->count(3)->create(['organization_id' => $this->organization->id]);

    $this->get('/clients')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('clients/index')
            ->has('clients.data', 3)
        );
});

it('displays the create client page', function () {
    $this->get('/clients/create')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('clients/create'));
});

it('creates a client', function () {
    $this->post('/clients', [
        'name' => 'Test Client',
        'email' => 'client@example.com',
        'company' => 'Test Co',
    ])->assertRedirect('/clients');

    $this->assertDatabaseHas('clients', [
        'organization_id' => $this->organization->id,
        'name' => 'Test Client',
        'email' => 'client@example.com',
    ]);
});

it('validates required fields when creating a client', function () {
    $this->post('/clients', [])
        ->assertSessionHasErrors(['name']);
});

it('displays a client', function () {
    $client = Client::factory()->create(['organization_id' => $this->organization->id]);

    $this->get("/clients/{$client->id}")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('clients/show')
            ->where('client.id', $client->id)
        );
});

it('displays the edit client page', function () {
    $client = Client::factory()->create(['organization_id' => $this->organization->id]);

    $this->get("/clients/{$client->id}/edit")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('clients/edit')
            ->where('client.id', $client->id)
        );
});

it('updates a client', function () {
    $client = Client::factory()->create(['organization_id' => $this->organization->id]);

    $this->put("/clients/{$client->id}", [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ])->assertRedirect("/clients/{$client->id}");

    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'name' => 'Updated Name',
    ]);
});

it('deletes a client', function () {
    $client = Client::factory()->create(['organization_id' => $this->organization->id]);

    $this->delete("/clients/{$client->id}")
        ->assertRedirect('/clients');

    $this->assertDatabaseMissing('clients', ['id' => $client->id]);
});

it('prevents access to clients from other organizations', function () {
    $otherOrg = Organization::factory()->create();
    $client = Client::factory()->create(['organization_id' => $otherOrg->id]);

    $this->get("/clients/{$client->id}")
        ->assertForbidden();
});
