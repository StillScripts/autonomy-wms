<?php

namespace App\Policies;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganisationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can always view their organisations
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Organisation $organisation): bool
    {
        return $user->organisations->contains($organisation->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Users can create organisations
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Organisation $organisation): bool
    {
        return $organisation->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organisation $organisation): bool
    {
        return $organisation->users()
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Organisation $organisation): bool
    {
        return false; // No restore functionality
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Organisation $organisation): bool
    {
        return false; // No force delete functionality
    }

    public function manageSystemData(User $user, Organisation $organisation): bool
    {
        // Only users in the super org with admin/owner roles can manage system data
        if (!$organisation->is_super_org) {
            return false;
        }
        
        return $organisation->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }
} 