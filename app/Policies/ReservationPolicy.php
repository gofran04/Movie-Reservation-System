<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReservationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $authUser): bool
    {
        // Admins & general manager can see all
        if ($authUser->can('view-all-reservations')) {
            return true;
        }

        // clients can see their own list
        return $authUser->can('view-reservation');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $authUser, Reservation $reservation): bool
    {
        // Admins & managers can view any reservation
        if ($authUser->can('view-all-reservations')) {
            return true;
        }

        // Regular user: only their own reservation
        return $authUser->can('view-reservation') && $authUser->id === $reservation->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $authUser): bool
    {
        return $authUser->can('create-reservation');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reservation $reservation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Reservation $reservation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Reservation $reservation): bool
    {
        return false;
    }
}
