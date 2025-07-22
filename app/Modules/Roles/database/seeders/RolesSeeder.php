<?php

namespace App\Modules\Roles\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Roles\Models\Roles;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        Roles::create([
            "name" => "Sample Roles",
            "description" => "This is a sample Roles",
            "active" => true,
        ]);
    }
}