<?php

namespace App\Policies;

use App\Models\Hall;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HallPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $authUser): bool
    {
        return $authUser->can('view-all-halls');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $authUser, Hall $hall): bool
    {
        return $authUser->can('view-hall');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $authUser): bool
    {
        return $authUser->can('create-hall');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $authUser, Hall $hall): bool
    {
        return $authUser->can('edit-hall');
    }


    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Hall $hall): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Hall $hall): bool
    {
        return false;
    }
}
