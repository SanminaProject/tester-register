<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TesterCustomer;

class TesterCustomerPolicy extends BasePolicy
{
    public function view(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
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
        return $this->hasAnyRole($user, ['admin']);
    }
}
