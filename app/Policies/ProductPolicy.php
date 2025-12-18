<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
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

        // O, A and M can view products
        return $currentOrg->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin', 'member'])
            ->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        $currentOrg = $user->currentOrganisation();
        if (!$currentOrg) {
            return false;
        }

        // O, A and M can view products
        return $currentOrg->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin', 'member'])
            ->exists();
    }

    /**
     * Determine whether the user can sync products.
     */
    public function sync(User $user): bool
    {
        $currentOrg = $user->currentOrganisation();
        if (!$currentOrg) {
            return false;
        }

        $membership = $currentOrg->users()
            ->where('user_id', $user->id)
            ->first();

        if (!$membership) {
            return false;
        }

        // Owner and admin can always sync
        if (in_array($membership->role, ['owner', 'admin'])) {
            return true;
        }

        // Members need specific permissions
        return $membership->role === 'member' && $membership->hasPermission('sync_products');
    }

    /**
     * Determine whether the user can mark a product as hidden.
     */
    public function markHidden(User $user, Product $product): bool
    {
        $currentOrg = $user->currentOrganisation();
        if (!$currentOrg) {
            return false;
        }

        $membership = $currentOrg->users()
            ->where('user_id', $user->id)
            ->first();

        if (!$membership) {
            return false;
        }

        // Owner and admin can always mark as hidden
        if (in_array($membership->role, ['owner', 'admin'])) {
            return true;
        }

        // Members need specific permissions
        return $membership->role === 'member' && $membership->hasPermission('manage_products');
    }

    /**
     * Determine whether the user can view the product via API.
     */
    public function viewViaApi(User $user, Product $product): bool
    {
        // C and G can view products via API service
        return true;
    }
} 