<?php

namespace App\Policies;

use App\Models\ContentBlock;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContentBlockPolicy
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
    public function view(User $user, ContentBlock $contentBlock): bool
    {
        // O, A, M, MWP can view content blocks
        return $contentBlock->organisation->users()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can create content blocks.
     */
    public function create(User $user, $model = null): bool
    {
        $organisation = $model instanceof Organisation ? $model : $user->currentOrganisation();

        if (!$organisation) {
            return false;
        }

        $membership = $organisation->users()->where('user_id', $user->id)->first();
        $role = $membership?->pivot?->role;

        if (!$role) {
            return false;
        }

        if (in_array($role, ['owner', 'admin'])) {
            return true;
        }

        // Members need specific permissions
        return $role === 'member' && $membership->pivot->hasPermission('create_content_blocks');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ContentBlock $contentBlock): bool
    {
        // O, A and MWP can edit content blocks
        $membership = $contentBlock->organisation->users()
            ->where('user_id', $user->id)
            ->first();

        if (!$membership) {
            return false;
        }

        // Owner and admin can always update
        if (in_array($membership->pivot->role, ['owner', 'admin'])) {
            return true;
        }

        // Members need specific permissions
        return $membership->pivot->role === 'member' && $membership->pivot->hasPermission('edit_content_blocks');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ContentBlock $contentBlock): bool
    {
        // O, A and MWP can delete content blocks
        $membership = $contentBlock->organisation->users()
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
        return $membership->role === 'member' && $membership->hasPermission('delete_content_blocks');
    }

    /**
     * Determine whether the user can view the content block via API.
     */
    public function viewViaApi(User $user, ContentBlock $contentBlock): bool
    {
        // C and G can view content blocks via API service
        return true;
    }
} 