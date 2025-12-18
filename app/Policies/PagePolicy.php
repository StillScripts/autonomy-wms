<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PagePolicy
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

        // O, A and MWP can view pages
        $membership = $currentOrg->users()
            ->where('user_id', $user->id)
            ->first();

        if (!$membership) {
            return false;
        }

        // Owner and admin can always view
        if (in_array($membership->role, ['owner', 'admin'])) {
            return true;
        }

        // Members need specific permissions
        return $membership->role === 'member' && $membership->hasPermission('view_pages');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Page $page): bool
    {
        $currentOrg = $user->currentOrganisation();
        if (!$currentOrg) {
            return false;
        }

        // O, A and MWP can view pages
        $membership = $currentOrg->users()
            ->where('user_id', $user->id)
            ->first();

        if (!$membership) {
            return false;
        }

        // Owner and admin can always view
        if (in_array($membership->role, ['owner', 'admin'])) {
            return true;
        }

        // Members need specific permissions
        return $membership->role === 'member' && $membership->hasPermission('view_pages');
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

        // O, A and MWP can create pages
        $membership = $currentOrg->users()
            ->where('user_id', $user->id)
            ->first();

        if (!$membership) {
            return false;
        }

        // Owner and admin can always create
        if (in_array($membership->role, ['owner', 'admin'])) {
            return true;
        }

        // Members need specific permissions
        return $membership->role === 'member' && $membership->hasPermission('create_pages');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Page $page): bool
    {
        $currentOrg = $user->currentOrganisation();
        if (!$currentOrg) {
            return false;
        }

        // O, A and MWP can edit pages
        $membership = $currentOrg->users()
            ->where('user_id', $user->id)
            ->first();

        if (!$membership) {
            return false;
        }

        // Owner and admin can always update
        if (in_array($membership->role, ['owner', 'admin'])) {
            return true;
        }

        // Members need specific permissions
        return $membership->role === 'member' && $membership->hasPermission('edit_pages');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Page $page): bool
    {
        $currentOrg = $user->currentOrganisation();
        if (!$currentOrg) {
            return false;
        }

        // O, A and MWP can delete pages
        $membership = $currentOrg->users()
            ->where('user_id', $user->id)
            ->first();

        if (!$membership) {
            return false;
        }

        // Owner and admin can always delete
        if (in_array($membership->role, ['owner', 'admin'])) {
            return true;
        }

        // Members need specific permissions
        return $membership->role === 'member' && $membership->hasPermission('delete_pages');
    }

    /**
     * Determine whether the user can view the page via API.
     */
    public function viewViaApi(User $user, Page $page): bool
    {
        // C and G can view pages via API service
        return true;
    }
} 