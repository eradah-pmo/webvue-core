<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Department;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $itDepartment = Department::where('code', 'IT')->first();
        $hrDepartment = Department::where('code', 'HR')->first();

        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Administrator',
            'first_name' => 'Super',
            'last_name' => 'Administrator',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'department_id' => $itDepartment?->id,
            'locale' => 'en',
            'active' => true,
        ]);
        $superAdmin->assignRole('super-admin');

        // Create Admin
        $admin = User::create([
            'name' => 'System Admin',
            'first_name' => 'System',
            'last_name' => 'Admin',
            'email' => 'sysadmin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'department_id' => $itDepartment?->id,
            'locale' => 'en',
            'active' => true,
        ]);
        $admin->assignRole('admin');

        // Create Manager
        $manager = User::create([
            'name' => 'Department Manager',
            'first_name' => 'Department',
            'last_name' => 'Manager',
            'email' => 'manager@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'department_id' => $hrDepartment?->id,
            'locale' => 'en',
            'active' => true,
        ]);
        $manager->assignRole('manager');

        // Create Regular User
        $user = User::create([
            'name' => 'Regular User',
            'first_name' => 'Regular',
            'last_name' => 'User',
            'email' => 'user@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'department_id' => $hrDepartment?->id,
            'locale' => 'en',
            'active' => true,
        ]);
        $user->assignRole('user');

        // Update department managers
        if ($itDepartment) {
            $itDepartment->update(['manager_id' => $superAdmin->id]);
        }
        if ($hrDepartment) {
            $hrDepartment->update(['manager_id' => $manager->id]);
        }

        $this->command->info('Users seeded successfully');
        $this->command->info('Login credentials:');
        $this->command->info('Super Admin: admin@example.com / password');
        $this->command->info('Admin: sysadmin@example.com / password');
        $this->command->info('Manager: manager@example.com / password');
        $this->command->info('User: user@example.com / password');
    }
}
