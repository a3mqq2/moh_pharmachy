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
            $table->timestamp('expires_at')->nullable()->after('activated_at');
            $table->timestamp('last_renewed_at')->nullable()->after('expires_at');
        });

        Schema::table('foreign_companies', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('activated_at');
            $table->timestamp('last_renewed_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('local_companies', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'last_renewed_at']);
        });

        Schema::table('foreign_companies', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'last_renewed_at']);
        });
    }
};
