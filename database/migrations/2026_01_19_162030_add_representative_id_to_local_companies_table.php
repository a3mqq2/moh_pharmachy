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
            $table->foreignId('representative_id')->nullable()->after('user_id')->constrained('company_representatives')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('local_companies', function (Blueprint $table) {
            $table->dropForeign(['representative_id']);
            $table->dropColumn('representative_id');
        });
    }
};
