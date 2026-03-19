<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('local_company_invoices', function (Blueprint $table) {
            $table->index('receipt_status');
            $table->index('type');
        });

        Schema::table('foreign_company_invoices', function (Blueprint $table) {
            $table->index('receipt_status');
        });

        Schema::table('foreign_companies', function (Blueprint $table) {
            $table->unique('registration_number');
            $table->index('is_pre_registered');
        });

        Schema::table('local_companies', function (Blueprint $table) {
            $table->index('is_pre_registered');
        });

        Schema::table('pharmaceutical_products', function (Blueprint $table) {
            $table->index('is_pre_registered');
        });
    }

    public function down(): void
    {
        Schema::table('local_company_invoices', function (Blueprint $table) {
            $table->dropIndex(['receipt_status']);
            $table->dropIndex(['type']);
        });

        Schema::table('foreign_company_invoices', function (Blueprint $table) {
            $table->dropIndex(['receipt_status']);
        });

        Schema::table('foreign_companies', function (Blueprint $table) {
            $table->dropUnique(['registration_number']);
            $table->dropIndex(['is_pre_registered']);
        });

        Schema::table('local_companies', function (Blueprint $table) {
            $table->dropIndex(['is_pre_registered']);
        });

        Schema::table('pharmaceutical_products', function (Blueprint $table) {
            $table->dropIndex(['is_pre_registered']);
        });
    }
};
