<?php

namespace App\Policies;

use App\Models\ThirdPartyVariable;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class ThirdPartyVariablePolicy
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
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ThirdPartyVariable $thirdPartyVariable): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperOrgAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ThirdPartyVariable $thirdPartyVariable): bool
    {
        return $user->isSuperOrgAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ThirdPartyVariable $thirdPartyVariable): bool
    {
        return $user->isSuperOrgAdmin();
    }
}
