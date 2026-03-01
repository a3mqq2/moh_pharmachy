<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $keysToKeep = [
            'local_company_validity_years',
            'local_company_renewal_fee',
            'foreign_company_validity_years',
            'foreign_company_renewal_fee',
        ];

        Setting::whereIn('group', ['local_companies', 'foreign_companies'])
            ->whereNotIn('key', $keysToKeep)
            ->delete();
    }

    public function down(): void
    {
    }
};
