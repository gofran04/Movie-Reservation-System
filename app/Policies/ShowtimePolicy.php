<?php

namespace App\Policies;

use App\Models\Showtime;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShowtimePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $auhtUser): bool
    {
        return $auhtUser->can('view-all-showtimes');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $auhtUser, Showtime $showtime): bool
    {
        return $auhtUser->can('view-showtime');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $auhtUser): bool
    {
        return $auhtUser->can('create-showtime');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $auhtUser, Showtime $showtime): bool
    {
        return $auhtUser->can('edit-showtime');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $auhtUser, Showtime $showtime)
    {
        if($showtime->reservations()->count()){
            return Response::deny('Cannot delete a showtime with existing reservations.');
        }

        return $auhtUser->can('delete-showtime') 
            && $showtime->start_time > now();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Showtime $showtime): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Showtime $showtime): bool
    {
        return false;
    }
}
