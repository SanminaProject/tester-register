<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SparePart;

class SparePartPolicy extends BasePolicy
{
    public function view(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    public function create(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function update(User $user, SparePart $part): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function delete(User $user, SparePart $part): bool
    {
        return $this->hasAnyRole($user, ['admin']);
    }
}
