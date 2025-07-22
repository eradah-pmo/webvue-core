<?php

namespace App\Modules\Departments\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Departments\Models\Departments;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        Departments::create([
            "name" => "Sample Departments",
            "description" => "This is a sample Departments",
            "active" => true,
        ]);
    }
}