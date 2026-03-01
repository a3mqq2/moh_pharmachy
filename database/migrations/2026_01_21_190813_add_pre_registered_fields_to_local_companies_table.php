<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('local_companies', function (Blueprint $table) {
            $table->boolean('is_pre_registered')->default(false)->after('status');
            $table->string('pre_registration_number')->nullable()->after('is_pre_registered');
            $table->year('pre_registration_year')->nullable()->after('pre_registration_number');
        });
    }

    public function down(): void
    {
        Schema::table('local_companies', function (Blueprint $table) {
            $table->dropColumn(['is_pre_registered', 'pre_registration_number', 'pre_registration_year']);
        });
    }
};
