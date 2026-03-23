<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Fixture;

class FixturePolicy extends BasePolicy
{
    public function view(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'technician', 'guest']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function update(User $user, Fixture $fixture): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function delete(User $user, Fixture $fixture): bool
    {
        return $user->hasRole(['admin']);
    }
}
