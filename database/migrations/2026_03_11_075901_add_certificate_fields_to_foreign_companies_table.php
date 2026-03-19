<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('foreign_companies', function (Blueprint $table) {
            $table->string('registration_number')->nullable()->after('status');
            $table->string('meeting_number')->nullable()->after('registration_number');
            $table->date('meeting_date')->nullable()->after('meeting_number');
        });
    }

    public function down(): void
    {
        Schema::table('foreign_companies', function (Blueprint $table) {
            $table->dropColumn(['registration_number', 'meeting_number', 'meeting_date']);
        });
    }
};
