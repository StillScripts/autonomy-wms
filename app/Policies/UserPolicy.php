<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Organisation;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Organisation $organisation): bool
    {
        return $organisation->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin', 'member'])
            ->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Check if both users share an overlapping organisation
        $overlappingOrgs = $user->organisations()
            ->whereIn('organisations.id', $model->organisations()->pluck('organisations.id'))
            ->get();

        if ($overlappingOrgs->isEmpty()) {
            return false;
        }

        // Check if the user has a valid role in any of the overlapping organisations
        foreach ($overlappingOrgs as $org) {
            $role = $org->users()->where('user_id', $user->id)->value('role');
            if (in_array($role, ['owner', 'admin', 'member'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Organisation $organisation): bool
    {
        // Only owners and admins can create users
        return $organisation->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }
} 