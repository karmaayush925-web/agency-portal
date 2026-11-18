<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Grant full access to administrators for all invoice actions.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view the list of invoices.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'client'], true);
    }

    /**
     * Determine whether the user can view a specific invoice.
     * Prevents IDOR by restricting clients to their own invoices only.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->isManager()) {
            return true;
        }

        return $user->isClient()
            && $user->clientProfile
            && $user->clientProfile->id === $invoice->client_id;
    }

    /**
     * Determine whether the user can create new invoices.
     */
    public function create(User $user): bool
    {
        return $user->isManager();
    }

    /**
     * Determine whether the user can update the invoice.
     * Paid invoices are locked from further edits.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        return $user->isManager() && $invoice->status !== 'paid';
    }

    /**
     * Determine whether the user can delete the invoice.
     * Handled exclusively by administrators via the before() hook.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return false;
    }
}