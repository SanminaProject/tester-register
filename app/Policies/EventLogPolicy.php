<?php

namespace App\Policies;

use App\Models\User;
use App\Models\EventLog;

class EventLogPolicy extends BasePolicy
{
    public function view(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    public function create(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    public function update(User $user, EventLog $log): bool
    {
        return false; // Event logs cannot be updated
    }

    public function delete(User $user, EventLog $log): bool
    {
        return $this->hasAnyRole($user, ['admin']);
    }
}
