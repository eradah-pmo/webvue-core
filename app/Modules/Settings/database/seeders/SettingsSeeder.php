<?php

namespace App\Modules\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Settings\Models\Settings;
use App\Modules\Settings\Data\DefaultSettingsData;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedDefaultSettings();
    }

    /**
     * Seed default system settings.
     */
    private function seedDefaultSettings(): void
    {
        $settings = DefaultSettingsData::getAllSettings();
        
        foreach ($settings as $setting) {
            Settings::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
