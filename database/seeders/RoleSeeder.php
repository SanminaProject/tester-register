<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Admin',
            'Manager',
            'Maintenance Technician',
            'Calibration Specialist',
            'Guest',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }

        $defaultUsers = [
            [
                'email' => 'admin@example.com',
                'name' => 'Admin User',
                'password' => '12345678',
                'phone' => '123-456-7890',
                'role' => 'Admin',
            ],
            [
                'email' => 'manager@example.com',
                'name' => 'Manager User',
                'password' => '12345678',
                'phone' => '123-456-7891',
                'role' => 'Manager',
            ],
            [
                'email' => 'technician@example.com',
                'name' => 'Technician User',
                'password' => '12345678',
                'phone' => '123-456-7892',
                'role' => 'Calibration Specialist',
            ],
            [
                'email' => 'guest@example.com',
                'name' => 'Guest User',
                'password' => '12345678',
                'phone' => '123-456-7893',
                'role' => 'Guest',
            ],
        ];

        foreach ($defaultUsers as $entry) {
            $user = User::firstOrCreate(
                ['email' => $entry['email']],
                [
                    'name' => $entry['name'],
                    'password' => Hash::make($entry['password']),
                    'phone' => $entry['phone'],
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$entry['role']]);
        }
    }
}
