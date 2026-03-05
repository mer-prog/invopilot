<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create([
        'owner_id' => $this->user->id,
    ]);
    $this->organization->users()->attach($this->user->id, ['role' => OrganizationRole::Owner->value]);

    $this->actingAs($this->user);
    session(['current_organization_id' => $this->organization->id]);
});

it('displays the api tokens page', function () {
    $this->get('/settings/api-tokens')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('settings/api-tokens')
            ->has('tokens')
        );
});

it('creates a new api token', function () {
    $this->post('/settings/api-tokens', [
        'name' => 'Test Token',
    ])->assertRedirect();

    expect($this->user->tokens)->toHaveCount(1);
    expect($this->user->tokens->first()->name)->toBe('Test Token');
});

it('validates token name is required', function () {
    $this->post('/settings/api-tokens', [
        'name' => '',
    ])->assertSessionHasErrors('name');
});

it('revokes an api token', function () {
    $token = $this->user->createToken('Test Token');

    $this->delete('/settings/api-tokens/'.$token->accessToken->id)
        ->assertRedirect();

    expect($this->user->fresh()->tokens)->toHaveCount(0);
});

it('cannot revoke another users token', function () {
    $otherUser = User::factory()->create();
    $token = $otherUser->createToken('Other Token');

    $this->delete('/settings/api-tokens/'.$token->accessToken->id)
        ->assertRedirect();

    // Token should still exist because it belongs to another user
    expect($otherUser->fresh()->tokens)->toHaveCount(1);
});
