<?php

namespace App\Policies;

use App\Models\User;

class BasePolicy
{
    /**
     * Role aliases used to bridge legacy web roles and API role names.
     *
     * @var array<string, array<int, string>>
     */
    protected const ROLE_ALIASES = [
        'admin' => ['admin', 'Admin'],
        'manager' => ['manager', 'Maintenance Technician'],
        'technician' => ['technician', 'Calibration Specialist'],
        'guest' => ['guest', 'Guest'],
    ];

    /**
     * Check if user has any role from canonical role names.
     *
     * @param  array<int, string>  $roles
     */
    protected function hasAnyRole(User $user, array $roles): bool
    {
        $expandedRoles = [];

        foreach ($roles as $role) {
            $expandedRoles = array_merge(
                $expandedRoles,
                self::ROLE_ALIASES[$role] ?? [$role],
            );
        }

        return $user->hasRole(array_values(array_unique($expandedRoles)));
    }
}
