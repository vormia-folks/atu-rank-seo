<?php

namespace Vormia\ATURankSEO\Database\Seeders;

use Illuminate\Database\Seeder;
use Vormia\ATURankSEO\Models\RankSeoSettings;

class ATURankSEOSeeder extends Seeder
{
    public function run(): void
    {
        $settings = RankSeoSettings::first();

        if (! $settings) {
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

            $this->command?->info('Default SEO settings created.');
        } else {
            $this->command?->info('SEO settings already exist.');
        }
    }
}
