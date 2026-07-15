<?php

namespace App\Policies;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MoviePolicy
{
    /**
     * Allow any user to view the list of movies.
     */
    public function viewAny(?User $authUser): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $authUser, Movie $movie): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $authUser): bool
    {
        return $authUser->can('create-movie');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $authUser, Movie $movie): bool
    {
        return $authUser->can('edit-movie');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $authUser, Movie $movie): bool
    {
        return $authUser->can('delete-movie');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Movie $movie): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Movie $movie): bool
    {
        return false;
    }
}
