<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Tester;

class TesterPolicy extends BasePolicy
{
    public function view(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'technician', 'guest']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function update(User $user, Tester $tester): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function delete(User $user, Tester $tester): bool
    {
        return $user->hasRole(['admin']);
    }
}
