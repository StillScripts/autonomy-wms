<?php

namespace App\Policies;

use App\Models\PrivateFile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrivateFilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any private files.
     */
    public function viewAny(User $user): bool
    {
        // User must have a current organisation
        return $user->currentOrganisation() !== null;
    }

    /**
     * Determine whether the user can view the private file.
     */
    public function view(User $user, PrivateFile $privateFile): bool
    {
        // User's current organisation must match the private file's organisation
        $currentOrganisation = $user->currentOrganisation();
        return $currentOrganisation && $currentOrganisation->id === $privateFile->organisation_id;
    }

    /**
     * Determine whether the user can create private files.
     */
    public function create(User $user): bool
    {
        // User must have a current organisation
        return $user->currentOrganisation() !== null;
    }

    /**
     * Determine whether the user can update the private file.
     */
    public function update(User $user, PrivateFile $privateFile): bool
    {
        // User's current organisation must match the private file's organisation
        $currentOrganisation = $user->currentOrganisation();
        return $currentOrganisation && $currentOrganisation->id === $privateFile->organisation_id;
    }

    /**
     * Determine whether the user can delete the private file.
     */
    public function delete(User $user, PrivateFile $privateFile): bool
    {
        // User's current organisation must match the private file's organisation
        $currentOrganisation = $user->currentOrganisation();
        return $currentOrganisation && $currentOrganisation->id === $privateFile->organisation_id;
    }
} 