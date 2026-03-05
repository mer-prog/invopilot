<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        $orgId = session('current_organization_id');

        return $orgId && $user->organizations()->where('organizations.id', $orgId)->exists();
    }

    public function view(User $user, Client $client): bool
    {
        return $client->organization_id === (int) session('current_organization_id');
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Client $client): bool
    {
        return $client->organization_id === (int) session('current_organization_id');
    }

    public function delete(User $user, Client $client): bool
    {
        return $client->organization_id === (int) session('current_organization_id');
    }
}
