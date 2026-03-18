<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
        'Admin',
        'Maintenance Technician',
        'Calibration Specialist'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);

            // create a default admin user and assign the Admin role
            $user = User::firstOrCreate(
                ['email' => 'admin@example.com'],
                ['name' => 'Admin User', 'password' => '12345678']
            );

            $user->assignRole('Admin');
        }
    }
}
