<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Core\Services\RBACService;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rbacService = new RBACService();
        $rbacService->initializeRBAC();
        
        $this->command->info('Roles and permissions seeded successfully');
    }
}
