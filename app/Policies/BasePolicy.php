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

    /**
     * Check if user can view
     */
    public function view(User $user): bool
    {
        return true;
    }

    /**
     * Check if user can create
     */
    public function create(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    /**
     * Check if user can update
     */
    public function update(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager', 'technician']);
    }

    /**
     * Check if user can delete
     */
    public function delete(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin']);
    }
}
