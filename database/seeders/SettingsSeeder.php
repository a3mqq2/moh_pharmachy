<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Foreign Company Settings
            [
                'key' => 'foreign_company_annual_fee',
                'value' => '5000',
                'group' => 'foreign_companies',
                'type' => 'number',
                'label' => 'رسوم التجديد السنوي للشركات الأجنبية',
                'description' => 'قيمة الرسوم السنوية للشركات الأجنبية (بالدينار الليبي)',
            ],
            [
                'key' => 'foreign_company_initial_fee',
                'value' => '5000',
                'group' => 'foreign_companies',
                'type' => 'number',
                'label' => 'رسوم التسجيل الأولي للشركات الأجنبية',
                'description' => 'قيمة رسوم التسجيل الأولي للشركات الأجنبية (بالدينار الليبي)',
            ],

            // Local Company Settings
            [
                'key' => 'local_company_annual_fee',
                'value' => '1000',
                'group' => 'local_companies',
                'type' => 'number',
                'label' => 'رسوم التجديد السنوي للشركات المحلية',
                'description' => 'قيمة الرسوم السنوية للشركات المحلية (بالدينار الليبي)',
            ],
            [
                'key' => 'local_company_initial_fee',
                'value' => '1000',
                'group' => 'local_companies',
                'type' => 'number',
                'label' => 'رسوم التسجيل الأولي للشركات المحلية',
                'description' => 'قيمة رسوم التسجيل الأولي للشركات المحلية (بالدينار الليبي)',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
