<?php

namespace App\Policies;

use App\Models\DaroodCount;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DaroodCountPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function modify(User $user, DaroodCount $count): Response
    {
        return $user->id === $count->user_id
            ? Response::allow()
            : Response::deny('You are not authorized as you do not own this!');
    }
}
