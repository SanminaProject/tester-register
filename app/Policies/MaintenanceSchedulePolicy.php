<?php

namespace App\Policies;

use App\Models\TesterMaintenanceSchedule as MaintenanceSchedule;
use App\Models\User;

class MaintenanceSchedulePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    public function view(User $user, MaintenanceSchedule $maintenanceSchedule): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function update(User $user, MaintenanceSchedule $maintenanceSchedule): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    public function delete(User $user, MaintenanceSchedule $maintenanceSchedule): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function complete(User $user, MaintenanceSchedule $maintenanceSchedule): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }
}
