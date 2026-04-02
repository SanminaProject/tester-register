<?php

namespace App\Policies;

use App\Models\Tester;
use App\Models\User;

class TesterPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician', 'guest']);
    }

    public function view(User $user, Tester $tester): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function update(User $user, Tester $tester): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function delete(User $user, Tester $tester): bool
    {
        return $this->isAdmin($user);
    }

    public function updateStatus(User $user, Tester $tester): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }
}
