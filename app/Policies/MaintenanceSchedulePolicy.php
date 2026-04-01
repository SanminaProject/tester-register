<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MaintenanceSchedule;

class MaintenanceSchedulePolicy extends BasePolicy
{
    public function view(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    public function create(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function update(User $user, MaintenanceSchedule $schedule): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    public function delete(User $user, MaintenanceSchedule $schedule): bool
    {
        return $this->hasAnyRole($user, ['admin']);
    }
}
