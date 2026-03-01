<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('local_companies', function (Blueprint $table) {
            $table->timestamp('activated_at')->nullable()->after('status');
        });

        // Update existing active companies to have activated_at set
        DB::statement("UPDATE local_companies SET activated_at = updated_at WHERE status = 'active' AND activated_at IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('local_companies', function (Blueprint $table) {
            $table->dropColumn('activated_at');
        });
    }
};
