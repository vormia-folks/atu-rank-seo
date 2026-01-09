<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Vormia\ATURankSEO\Models\RankSeoSettings;

class ATURankSEOSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default settings if they don't exist
        $settings = RankSeoSettings::first();

        if (!$settings) {
            RankSeoSettings::create([
                'is_enabled' => true,
                'global_title' => null,
                'global_description' => null,
                'global_keywords' => null,
                'dynamic_variables' => [
                    'site_name' => config('app.name', 'My Site'),
                    'current_year' => date('Y'),
                ],
            ]);

            $this->command->info('✅ Default SEO settings created.');
        } else {
            $this->command->info('ℹ️  SEO settings already exist.');
        }
    }
}
