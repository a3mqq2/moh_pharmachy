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
        Schema::table('representative_otps', function (Blueprint $table) {
            // Change type column from enum to support password_reset
            $table->enum('type', ['registration', 'login', 'password_reset'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('representative_otps', function (Blueprint $table) {
            // Revert back to original enum values
            $table->enum('type', ['registration', 'login'])->change();
        });
    }
};
