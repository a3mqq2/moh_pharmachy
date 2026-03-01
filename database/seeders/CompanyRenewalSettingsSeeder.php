<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class CompanyRenewalSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'local_company_validity_years',
                'value' => '1',
                'type' => 'number',
                'label' => 'مدة صلاحية الشركة المحلية (بالسنوات)',
                'description' => 'عدد السنوات التي تكون الشركة المحلية مفعلة فيها قبل الحاجة للتجديد',
                'group' => 'local_companies',
            ],
            [
                'key' => 'local_company_renewal_fee',
                'value' => '500.00',
                'type' => 'number',
                'label' => 'رسوم تجديد الشركة المحلية',
                'description' => 'رسوم تجديد الترخيص للشركة المحلية',
                'group' => 'local_companies',
            ],
            [
                'key' => 'foreign_company_validity_years',
                'value' => '5',
                'type' => 'number',
                'label' => 'مدة صلاحية الشركة الأجنبية (بالسنوات)',
                'description' => 'عدد السنوات التي تكون الشركة الأجنبية مفعلة فيها قبل الحاجة للتجديد',
                'group' => 'foreign_companies',
            ],
            [
                'key' => 'foreign_company_renewal_fee',
                'value' => '1000.00',
                'type' => 'number',
                'label' => 'رسوم تجديد الشركة الأجنبية',
                'description' => 'رسوم تجديد الترخيص للشركة الأجنبية',
                'group' => 'foreign_companies',
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
