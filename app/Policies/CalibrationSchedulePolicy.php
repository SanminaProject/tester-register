<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CalibrationSchedule;

class CalibrationSchedulePolicy extends BasePolicy
{
    public function view(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    public function create(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
    }

    public function update(User $user, CalibrationSchedule $schedule): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    public function delete(User $user, CalibrationSchedule $schedule): bool
    {
        return $this->hasAnyRole($user, ['admin']);
    }
}
