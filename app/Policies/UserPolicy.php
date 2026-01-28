<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Actions that apply to the User resource as a whole
     * (index, create).
     */

    public function viewAny(User $authUser): bool
    {
        return $authUser->can('view-all-users');
    }

    public function create(User $authUser): bool
    {
        return $authUser->can('create-user');
    }

    /**
     * Actions that apply to a specific User model instance
     * (show, update, delete).
     */

    public function view(User $authUser, User $targetUser): bool
    {
        return $authUser->can('view-user');
    }

    public function update(User $authUser, User $targetUser): bool
    {
        return $authUser->can('edit-user');
    }

    public function delete(User $authUser, User $targetUser): bool
    {
        return $authUser->can('delete-user');
    }

    public function suspend(User $authUser, User $targetUser): bool
    {
        return $authUser->can('suspend-user') && $targetUser->status !== 'suspended' 
        && $targetUser->id !== $authUser->id && $targetUser->hasRole('admin');
    }

    public function activate(User $authUser, User $targetUser): bool
    {
        return $authUser->can('activate-user') && $targetUser->status !== 'active' 
        && $targetUser->id !== $authUser->id &&  $targetUser->hasRole('admin');
    }

    /**
     * Optional lifecycle actions
     */

    public function restore(User $authUser, User $targetUser): bool
    {
        return false;
    }

    public function forceDelete(User $authUser, User $targetUser): bool
    {
        return false;
    }
}
