<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SparePart;

class SparePartPolicy extends BasePolicy
{
    public function view(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'technician']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function update(User $user, SparePart $part): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function delete(User $user, SparePart $part): bool
    {
        return $user->hasRole(['admin']);
    }
}
