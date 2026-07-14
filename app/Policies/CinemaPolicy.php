<?php

namespace App\Policies;

use App\Models\Cinema;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CinemaPolicy
{
    public function update(User $authUser, Cinema $cinema): bool
    {
        return $authUser->can('edit-cinema');
    }
}
