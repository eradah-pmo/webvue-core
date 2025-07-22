<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Users',
                'display_name' => 'User Management',
                'description' => 'Manage system users, profiles, and authentication',
                'version' => '1.0.0',
                'active' => true,
                'critical' => true,
                'dependencies' => [],
                'permissions' => ['users.view', 'users.create', 'users.edit', 'users.delete'],
                'navigation' => [
                    'name' => 'users',
                    'href' => '/users',
                    'icon' => 'UsersIcon',
                    'order' => 10,
                ],
                'config' => [
                    'per_page' => 15,
                    'allow_registration' => false,
                    'require_email_verification' => true,
                ],
                'installed_at' => now(),
                'last_updated' => now(),
            ],
            [
                'name' => 'Roles',
                'display_name' => 'Role & Permission Management',
                'description' => 'Manage user roles, permissions, and access control',
                'version' => '1.0.0',
                'active' => true,
                'critical' => true,
                'dependencies' => ['Users'],
                'permissions' => ['roles.view', 'roles.create', 'roles.edit', 'roles.delete'],
                'navigation' => [
                    'name' => 'roles',
                    'href' => '/roles',
                    'icon' => 'ShieldCheckIcon',
                    'order' => 20,
                ],
                'config' => [
                    'per_page' => 15,
                    'allow_role_creation' => true,
                    'super_admin_protected' => true,
                ],
                'installed_at' => now(),
                'last_updated' => now(),
            ],
            [
                'name' => 'Departments',
                'display_name' => 'Department Management',
                'description' => 'Manage organizational departments and hierarchies',
                'version' => '1.0.0',
                'active' => true,
                'critical' => false,
                'dependencies' => ['Users'],
                'permissions' => ['departments.view', 'departments.create', 'departments.edit', 'departments.delete'],
                'navigation' => [
                    'name' => 'departments',
                    'href' => '/departments',
                    'icon' => 'BuildingOfficeIcon',
                    'order' => 30,
                ],
                'config' => [
                    'per_page' => 15,
                    'allow_nested_departments' => true,
                    'require_manager' => false,
                ],
                'installed_at' => now(),
                'last_updated' => now(),
            ],
            [
                'name' => 'ActivityLogs',
                'display_name' => 'Activity Logs',
                'description' => 'View and manage system activity logs',
                'version' => '1.0.0',
                'active' => true,
                'critical' => false,
                'dependencies' => ['Users'],
                'permissions' => ['system.logs', 'system.audit'],
                'navigation' => [
                    'name' => 'logs',
                    'href' => '/activity-logs',
                    'icon' => 'DocumentTextIcon',
                    'order' => 90,
                ],
                'config' => [
                    'per_page' => 25,
                    'retention_days' => 90,
                    'log_level' => 'info',
                ],
                'installed_at' => now(),
                'last_updated' => now(),
            ],
            [
                'name' => 'Settings',
                'display_name' => 'System Settings',
                'description' => 'Configure system-wide settings and preferences',
                'version' => '1.0.0',
                'active' => true,
                'critical' => true,
                'dependencies' => [],
                'permissions' => ['system.settings'],
                'navigation' => [
                    'name' => 'settings',
                    'href' => '/settings',
                    'icon' => 'Cog6ToothIcon',
                    'order' => 100,
                ],
                'config' => [
                    'allow_theme_change' => true,
                    'allow_language_change' => true,
                    'maintenance_mode' => false,
                ],
                'installed_at' => now(),
                'last_updated' => now(),
            ],
        ];

        foreach ($modules as $moduleData) {
            Module::create($moduleData);
        }

        $this->command->info('Modules seeded successfully');
    }
}
