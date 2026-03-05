<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        $orgId = session('current_organization_id');

        return $orgId && $user->organizations()->where('organizations.id', $orgId)->exists();
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $invoice->organization_id === (int) session('current_organization_id');
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $invoice->organization_id === (int) session('current_organization_id');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $invoice->organization_id === (int) session('current_organization_id');
    }

    public function send(User $user, Invoice $invoice): bool
    {
        return $this->update($user, $invoice) && $invoice->status->value === 'draft';
    }

    public function markPaid(User $user, Invoice $invoice): bool
    {
        return $this->update($user, $invoice) && in_array($invoice->status->value, ['sent', 'overdue']);
    }
}
