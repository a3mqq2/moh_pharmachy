<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmaceutical_products', function (Blueprint $table) {
            $table->string('registration_number')->nullable()->unique()->after('status');
        });

        Schema::table('foreign_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('foreign_companies', 'is_pre_registered')) {
                $table->boolean('is_pre_registered')->default(false)->after('registration_number');
                $table->string('pre_registration_number')->nullable()->after('is_pre_registered');
                $table->year('pre_registration_year')->nullable()->after('pre_registration_number');
            }
        });

        Schema::table('pharmaceutical_products', function (Blueprint $table) {
            $table->boolean('is_pre_registered')->default(false)->after('registration_number');
            $table->string('pre_registration_number')->nullable()->after('is_pre_registered');
            $table->year('pre_registration_year')->nullable()->after('pre_registration_number');
        });
    }

    public function down(): void
    {
        Schema::table('pharmaceutical_products', function (Blueprint $table) {
            $table->dropColumn(['registration_number', 'is_pre_registered', 'pre_registration_number', 'pre_registration_year']);
        });

        if (Schema::hasColumn('foreign_companies', 'is_pre_registered')) {
            Schema::table('foreign_companies', function (Blueprint $table) {
                $table->dropColumn(['is_pre_registered', 'pre_registration_number', 'pre_registration_year']);
            });
        }
    }
};
