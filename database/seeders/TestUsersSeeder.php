<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Users\Models\Users;
use Spatie\Permission\Models\Role;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users
        $users = [
            [
                'first_name' => 'أحمد',
                'last_name' => 'محمد',
                'name' => 'أحمد محمد',
                'email' => 'ahmed@example.com',
                'password' => bcrypt('password123'),
                'phone' => '+966501234567',
                'locale' => 'ar',
                'active' => true
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'name' => 'Sarah Johnson',
                'email' => 'sarah@example.com',
                'password' => bcrypt('password123'),
                'phone' => '+1234567890',
                'locale' => 'en',
                'active' => true
            ],
            [
                'first_name' => 'فاطمة',
                'last_name' => 'علي',
                'name' => 'فاطمة علي',
                'email' => 'fatima@example.com',
                'password' => bcrypt('password123'),
                'phone' => '+966509876543',
                'locale' => 'ar',
                'active' => false
            ]
        ];

        foreach ($users as $userData) {
            $user = Users::create($userData);
            
            // Assign roles if they exist
            if (Role::where('name', 'user')->exists()) {
                $user->assignRole('user');
            }
        }

        $this->command->info('Test users created successfully!');
    }
}
