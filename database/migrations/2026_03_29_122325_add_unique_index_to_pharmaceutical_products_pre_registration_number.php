<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmaceutical_products', function (Blueprint $table) {
            $table->unique('pre_registration_number');
        });
    }

    public function down(): void
    {
        Schema::table('pharmaceutical_products', function (Blueprint $table) {
            $table->dropUnique(['pre_registration_number']);
        });
    }
};
