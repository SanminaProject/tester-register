<?php

namespace App\Policies;

use App\Models\TesterCustomer;
use App\Models\User;

class TesterCustomerPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function view(User $user, TesterCustomer $customer): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function update(User $user, TesterCustomer $customer): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function delete(User $user, TesterCustomer $customer): bool
    {
        return $this->isAdmin($user);
    }
}
