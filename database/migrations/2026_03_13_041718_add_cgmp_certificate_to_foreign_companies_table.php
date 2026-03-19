<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('foreign_companies', function (Blueprint $table) {
            $table->string('cgmp_certificate_path')->nullable()->after('suspension_reason');
            $table->string('cgmp_certificate_name')->nullable()->after('cgmp_certificate_path');
            $table->timestamp('cgmp_uploaded_at')->nullable()->after('cgmp_certificate_name');
        });
    }

    public function down(): void
    {
        Schema::table('foreign_companies', function (Blueprint $table) {
            $table->dropColumn(['cgmp_certificate_path', 'cgmp_certificate_name', 'cgmp_uploaded_at']);
        });
    }
};
