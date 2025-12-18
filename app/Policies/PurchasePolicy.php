<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $currentOrg = $user->currentOrganisation();
        if (!$currentOrg) {
            return false;
        }

        // O, A and M can view all purchases
        return $currentOrg->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin', 'member'])
            ->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Purchase $purchase): bool
    {
        $currentOrg = $user->currentOrganisation();
        if (!$currentOrg) {
            return false;
        }

        // O, A and M can view all purchases
        if ($currentOrg->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin', 'member'])
            ->exists()) {
            return true;
        }

        // C can view their own purchases
        return $purchase->user_id === $user->id;
    }

    /**
     * Determine whether the user can create a test purchase.
     */
    public function createTest(User $user): bool
    {
        $currentOrg = $user->currentOrganisation();
        if (!$currentOrg) {
            return false;
        }

        // O, A and M can do test purchases if test keys are active
        if ($currentOrg->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin', 'member'])
            ->exists()) {
            // Check if test keys are active
            return $currentOrg->hasActiveTestKeys();
        }

        return false;
    }

    /**
     * Determine whether the user can create a real purchase via API.
     */
    public function createViaApi(User $user): bool
    {
        // C can create real purchases via API service
        return true;
    }
} 