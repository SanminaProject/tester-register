<?php

namespace App\Policies;

use App\Models\TesterEventLog as EventLog;
use App\Models\User;

class EventLogPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    public function view(User $user, EventLog $eventLog): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    public function update(User $user, EventLog $eventLog): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    public function delete(User $user, EventLog $eventLog): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }
}
