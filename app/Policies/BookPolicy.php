<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function modify(User $user, Book $book): Response
    {
        return $user->id === $book->user_id
            ? Response::allow()
            : Response::deny('You do not own this book');
    }
}
