<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('foreign_company_documents')
            ->where('document_type', 'products_list')
            ->update(['document_type' => 'products_artwork_list']);

        DB::statement("ALTER TABLE foreign_company_documents MODIFY COLUMN document_type ENUM(
            'official_registration_request','agency_agreement','registration_forms','gmp_certificate',
            'fda_certificate','emea_certificate','cpp_certificate','fsc_certificate',
            'manufacturing_license','financial_report','site_master_file',
            'registration_certificates','exclusive_agency_contract',
            'products_artwork_list','pv_master_file','other'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE foreign_company_documents MODIFY COLUMN document_type ENUM(
            'official_registration_request','agency_agreement','registration_forms','gmp_certificate',
            'fda_certificate','emea_certificate','cpp_certificate','fsc_certificate',
            'manufacturing_license','financial_report','products_list','site_master_file',
            'registration_certificates','exclusive_agency_contract',
            'products_artwork_list','pv_master_file','other'
        ) NOT NULL");

        DB::table('foreign_company_documents')
            ->where('document_type', 'products_artwork_list')
            ->update(['document_type' => 'products_list']);
    }
};
