<?php

namespace App\Policies;

use App\Models\Seat;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SeatPolicy
{
    /**
     * Allow any user to view seats, even if they are not authenticated.
     */
    public function viewAny(?User $authUser): bool
    {
        return true;
    }

    /**
     * Allow any user to view a specific seat, even if they are not authenticated.
     */
    public function view(?User $authUser, Seat $seat): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $authUser, Seat $seat): bool
    {
        return $authUser->can('edit-seat', $seat);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Seat $seat): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Seat $seat): bool
    {
        return false;
    }
}
