<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Membership;
use App\Models\Organisation;
use Illuminate\Auth\Access\HandlesAuthorization;

class MembershipPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any memberships.
     */
    public function viewAny(User $user, Organisation $organisation): bool
    {
        return $organisation->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin', 'member'])
            ->exists();
    }

    /**
     * Determine whether the user can view the membership.
     */
    public function view(User $user, Membership $membership): bool
    {
        $organisation = $membership->organisation;
        
        return $organisation->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin', 'member'])
            ->exists();
    }

    /**
     * Determine whether the user can create memberships.
     */
    public function create(User $user, Organisation $organisation): bool
    {
        return $organisation->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Determine whether the user can update the membership.
     */
    public function update(User $user, Membership $membership): bool
    {
        $organisation = $membership->organisation;
        
        $userRole = $organisation->users()->where('user_id', $user->id)->value('role');
        $targetRole = $membership->role;

        if ($userRole === 'owner') {
            return true;
        }

        if ($userRole === 'admin' && $targetRole === 'member') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the membership.
     */
    public function delete(User $user, Membership $membership): bool
    {
        $organisation = $membership->organisation;
        
        $userRole = $organisation->users()->where('user_id', $user->id)->value('role');
        $targetRole = $membership->role;

        if ($userRole === 'owner') {
            return true;
        }

        if ($userRole === 'admin' && $targetRole === 'member') {
            return true;
        }

        if ($membership->user_id === $user->id) {
            if ($targetRole === 'owner') {
                return $organisation->users()
                    ->where('role', 'owner')
                    ->count() > 1;
            }
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can assign roles.
     */
    public function assignRole(User $user, Membership $membership): bool
    {
        $organisation = $membership->organisation;
        
        $userRole = $organisation->users()->where('user_id', $user->id)->value('role');
        $targetRole = $membership->role;

        return in_array($userRole, ['owner', 'admin']) && $targetRole === 'member';
    }
} 