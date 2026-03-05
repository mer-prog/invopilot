<?php

use App\Enums\OrganizationRole;
use App\Models\Client;
use App\Models\Organization;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create([
        'owner_id' => $this->user->id,
    ]);
    $this->organization->users()->attach($this->user->id, ['role' => OrganizationRole::Owner->value]);

    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

it('requires authentication', function () {
    $this->getJson('/api/clients')
        ->assertUnauthorized();
});

it('lists clients via api', function () {
    Client::factory()->count(3)->create(['organization_id' => $this->organization->id]);

    $this->withToken($this->token)
        ->getJson('/api/clients')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('creates a client via api', function () {
    $this->withToken($this->token)
        ->postJson('/api/clients', [
            'name' => 'API Client',
            'email' => 'api@example.com',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'API Client');

    expect(Client::query()->where('name', 'API Client')->exists())->toBeTrue();
});

it('shows a client via api', function () {
    $client = Client::factory()->create(['organization_id' => $this->organization->id]);

    $this->withToken($this->token)
        ->getJson("/api/clients/{$client->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $client->id);
});

it('updates a client via api', function () {
    $client = Client::factory()->create(['organization_id' => $this->organization->id]);

    $this->withToken($this->token)
        ->putJson("/api/clients/{$client->id}", [
            'name' => 'Updated Name',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated Name');
});

it('deletes a client via api', function () {
    $client = Client::factory()->create(['organization_id' => $this->organization->id]);

    $this->withToken($this->token)
        ->deleteJson("/api/clients/{$client->id}")
        ->assertNoContent();

    expect(Client::query()->find($client->id))->toBeNull();
});

it('prevents access to another organizations client', function () {
    $otherOrg = Organization::factory()->create();
    $client = Client::factory()->create(['organization_id' => $otherOrg->id]);

    $this->withToken($this->token)
        ->getJson("/api/clients/{$client->id}")
        ->assertForbidden();
});
