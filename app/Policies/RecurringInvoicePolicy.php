<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RecurringInvoice;
use App\Models\User;

class RecurringInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        $orgId = session('current_organization_id');

        return $orgId && $user->organizations()->where('organizations.id', $orgId)->exists();
    }

    public function view(User $user, RecurringInvoice $recurringInvoice): bool
    {
        return $recurringInvoice->organization_id === (int) session('current_organization_id');
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, RecurringInvoice $recurringInvoice): bool
    {
        return $recurringInvoice->organization_id === (int) session('current_organization_id');
    }

    public function delete(User $user, RecurringInvoice $recurringInvoice): bool
    {
        return $recurringInvoice->organization_id === (int) session('current_organization_id');
    }
}
