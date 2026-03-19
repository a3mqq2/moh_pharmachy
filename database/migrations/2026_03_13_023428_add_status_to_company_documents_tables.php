<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('local_company_documents', function (Blueprint $table) {
            $table->string('status')->default('approved')->after('notes');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('status');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });

        Schema::table('foreign_company_documents', function (Blueprint $table) {
            $table->string('status')->default('approved')->after('notes');
            $table->string('rejection_reason')->nullable()->after('status');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('rejection_reason');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });

        DB::table('local_company_documents')->update(['status' => 'approved']);
        DB::table('foreign_company_documents')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('local_company_documents', function (Blueprint $table) {
            $table->dropColumn(['status', 'reviewed_by', 'reviewed_at']);
        });

        Schema::table('foreign_company_documents', function (Blueprint $table) {
            $table->dropColumn(['status', 'rejection_reason', 'reviewed_by', 'reviewed_at']);
        });
    }
};
