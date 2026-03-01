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
        // Drop the old enum column
        Schema::table('local_companies', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Add the new enum column with updated values
        Schema::table('local_companies', function (Blueprint $table) {
            $table->enum('status', [
                'uploading_documents',  // رفع المستندات
                'pending',              // قيد المراجعة
                'approved',             // مقبولة (قيد السداد)
                'payment_review',       // قيد مراجعة الدفع
                'active',               // مفعلة
                'rejected',             // مرفوضة
                'suspended'             // معلقة
            ])->default('uploading_documents')->after('manager_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('local_companies', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('local_companies', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending')->after('manager_email');
        });
    }
};
