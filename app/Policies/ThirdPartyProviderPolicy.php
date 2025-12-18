<?php

namespace App\Policies;

use App\Models\ThirdPartyProvider;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class ThirdPartyProviderPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $currentOrg = $user->currentOrganisation();
        if (!$currentOrg) {
            return false;
        }

        // O and A can view third parties
        return $currentOrg->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ThirdPartyProvider $thirdPartyProvider): bool
    {
        $currentOrg = $user->currentOrganisation();
        if (!$currentOrg) {
            return false;
        }

        // O and A can view third parties
        return $currentOrg->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $currentOrg = $user->currentOrganisation();
        if (!$currentOrg) {
            return false;
        }

        // O and A can attach third parties
        return $currentOrg->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ThirdPartyProvider $thirdPartyProvider): bool
    {
        $currentOrg = $user->currentOrganisation();
        if (!$currentOrg) {
            return false;
        }

        // O and A can edit third parties
        return $currentOrg->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ThirdPartyProvider $thirdPartyProvider): bool
    {
        $currentOrg = $user->currentOrganisation();
        if (!$currentOrg) {
            return false;
        }

        // O and A can delete third parties
        return $currentOrg->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }
}
