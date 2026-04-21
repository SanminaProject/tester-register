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
                'first_name' => 'AdminFirst',
                'last_name' => 'AdminLast',
                'password' => '12345678',
                'phone' => '123-456-7890',
                'role' => 'Admin',
            ],
            [
                'email' => 'manager@example.com',
                'first_name' => 'ManagerFirst',
                'last_name' => 'ManagerLast',
                'password' => '12345678',
                'phone' => '123-456-7891',
                'role' => 'Manager',
            ],
            [
                'email' => 'technician@example.com',
                'first_name' => 'TechnicianFirst',
                'last_name' => 'TechnicianLast',
                'password' => '12345678',
                'phone' => '123-456-7892',
                'role' => 'Calibration Specialist',
            ],
            [
                'email' => 'guest@example.com',
                'first_name' => 'GuestFirst',
                'last_name' => 'GuestLast',
                'password' => '12345678',
                'phone' => '123-456-7893',
                'role' => 'Guest',
            ],
            [
                'email' => 'test@example.com',
                'first_name' => 'TestFirst',
                'last_name' => 'TestLast',
                'password' => 'password123',
                'phone' => '123-456-7899',
                'role' => 'Guest',
            ],
        ];

        foreach ($defaultUsers as $entry) {
            $user = User::firstOrCreate(
                ['email' => $entry['email']],
                [
                    'first_name' => $entry['first_name'],
                    'last_name' => $entry['last_name'],
                    'password' => Hash::make($entry['password']),
                    'phone' => $entry['phone'],
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$entry['role']]);
        }
    }
}
