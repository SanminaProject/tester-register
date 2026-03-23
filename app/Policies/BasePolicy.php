<?php

namespace App\Policies;

use App\Models\User;

class BasePolicy
{
    /**
     * Check if user can view
     */
    public function view(User $user): bool
    {
        return true;
    }

    /**
     * Check if user can create
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'technician']);
    }

    /**
     * Check if user can update
     */
    public function update(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'technician']);
    }

    /**
     * Check if user can delete
     */
    public function delete(User $user): bool
    {
        return $user->hasRole(['admin']);
    }
}
