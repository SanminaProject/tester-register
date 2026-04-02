<?php

namespace App\Policies;

use App\Models\User;

class BasePolicy
{
    /**
     * Role aliases keep policy checks compatible with legacy and canonical role names.
     *
     * @var array<string, list<string>>
     */
    protected const ROLE_ALIASES = [
        'admin' => ['admin', 'Admin'],
        'manager' => ['manager', 'Manager', 'Maintenance Technician'],
        'technician' => ['technician', 'Technician', 'Calibration Specialist'],
        'guest' => ['guest', 'Guest'],
    ];

    /**
     * @param list<string> $roles
     */
    protected function hasAnyRole(User $user, array $roles): bool
    {
        $expandedRoles = [];

        foreach ($roles as $role) {
            $canonicalRole = strtolower($role);
            $aliases = self::ROLE_ALIASES[$canonicalRole] ?? [$role];

            foreach ($aliases as $alias) {
                $expandedRoles[] = $alias;
            }
        }

        return $user->hasAnyRole(array_values(array_unique($expandedRoles)));
    }

    protected function isAdmin(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin']);
    }
}
