<?php

namespace App\Policies;

use App\Models\SparePart;
use App\Models\User;

class SparePartPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician', 'guest']);
    }

    public function view(User $user, SparePart $sparePart): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function update(User $user, SparePart $sparePart): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function delete(User $user, SparePart $sparePart): bool
    {
        return $this->isAdmin($user);
    }
}
