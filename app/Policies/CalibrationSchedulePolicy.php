<?php

namespace App\Policies;

use App\Models\TesterCalibrationSchedule as CalibrationSchedule;
use App\Models\User;

class CalibrationSchedulePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    public function view(User $user, CalibrationSchedule $calibrationSchedule): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function update(User $user, CalibrationSchedule $calibrationSchedule): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    public function delete(User $user, CalibrationSchedule $calibrationSchedule): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function complete(User $user, CalibrationSchedule $calibrationSchedule): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }
}
