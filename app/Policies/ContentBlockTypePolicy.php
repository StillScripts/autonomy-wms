<?php

namespace App\Policies;

use App\Models\ContentBlockType;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContentBlockTypePolicy
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
    public function view(User $user, ContentBlockType $contentBlockType): bool
    {
        return $user->organisations->contains($contentBlockType->organisation_id);
    }

    /**
     * Determine whether the user can create content block types for the organisation.
     */
    public function create(User $user, Organisation $organisation): bool
    {
        return $organisation->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ContentBlockType $contentBlockType): bool
    {
        return $contentBlockType->organisation
            ->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ContentBlockType $contentBlockType): bool
    {
        return $contentBlockType->organisation
            ->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])  
            ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ContentBlockType $contentBlockType): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ContentBlockType $contentBlockType): bool
    {
        return false;
    }
} 