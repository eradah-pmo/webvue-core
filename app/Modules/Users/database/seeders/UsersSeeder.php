<?php

namespace App\Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Users\Models\Users;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        Users::create([
            "name" => "Sample Users",
            "description" => "This is a sample Users",
            "active" => true,
        ]);
    }
}