<?php

namespace App\Policies;

use App\Models\Organisation;
use App\Models\User;
use App\Models\Website;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class WebsitePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->currentOrganisation() !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Website $website): bool
    {
        // Check if user is in the website's organisation
        return $user->organisations->contains($website->organisation_id);
    }

    /**
     * Determine whether the user can create websites for the organisation.
     */
    public function create(User $user, Organisation $organisation): bool
    {
        // O, A and M can create a website
        return $organisation->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin', 'member'])
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Website $website): bool
    {
        // O, A and M can edit a website
        return $website->organisation->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin', 'member'])
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Website $website): bool
    {
        // Only O and A can delete a website
        return $website->organisation->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Website $website): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Website $website): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the website via API.
     */
    public function viewViaApi(User $user, Website $website): bool
    {
        // C and G can view a website via API service
        return true;
    }
}
