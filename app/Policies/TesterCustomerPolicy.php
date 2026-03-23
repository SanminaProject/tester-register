<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TesterCustomer;

class TesterCustomerPolicy extends BasePolicy
{
    public function view(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'technician', 'guest']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function update(User $user, TesterCustomer $customer): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function delete(User $user, TesterCustomer $customer): bool
    {
        return $user->hasRole(['admin']);
    }
}
