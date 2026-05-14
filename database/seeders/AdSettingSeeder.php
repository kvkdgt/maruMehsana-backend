<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            [
                'placement_key' => 'quiz_retry',
                'name' => 'Quiz Retry Attempt',
                'is_active' => true,
                'ad_unit_id_android' => 'ca-app-pub-4135252800687586/6418610868'
            ],
            [
                'placement_key' => 'quiz_double',
                'name' => 'Quiz Double Points',
                'is_active' => true,
                'ad_unit_id_android' => 'ca-app-pub-4135252800687586/6418610868'
            ],
            [
                'placement_key' => 'home_banner',
                'name' => 'Home Screen Banner',
                'is_active' => true,
                'ad_unit_id_android' => 'ca-app-pub-4135252800687586/1006502081'
            ],
            [
                'placement_key' => 'news_list_banner',
                'name' => 'News List Banner',
                'is_active' => true,
                'ad_unit_id_android' => 'ca-app-pub-4135252800687586/1006502081'
            ],
            [
                'placement_key' => 'job_list_banner',
                'name' => 'Job List Banner',
                'is_active' => true,
                'ad_unit_id_android' => 'ca-app-pub-4135252800687586/1006502081'
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\AdSetting::updateOrCreate(
                ['placement_key' => $setting['placement_key']],
                $setting
            );
        }
    }
}
