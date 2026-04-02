<?php

namespace App\Policies;

use App\Models\Fixture;
use App\Models\User;

class FixturePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician', 'guest']);
    }

    public function view(User $user, Fixture $fixture): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function update(User $user, Fixture $fixture): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function delete(User $user, Fixture $fixture): bool
    {
        return $this->isAdmin($user);
    }
}
