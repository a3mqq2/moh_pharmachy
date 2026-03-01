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
        Schema::table('local_company_invoices', function (Blueprint $table) {
            $table->enum('status', ['unpaid', 'pending_review', 'paid', 'rejected'])->default('unpaid')->change();
            $table->timestamp('receipt_uploaded_at')->nullable()->after('receipt_path');
            $table->enum('receipt_status', ['pending', 'approved', 'rejected'])->nullable()->after('receipt_uploaded_at');
            $table->text('receipt_rejection_reason')->nullable()->after('receipt_status');
            $table->foreignId('receipt_reviewed_by')->nullable()->after('receipt_rejection_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('receipt_reviewed_at')->nullable()->after('receipt_reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('local_company_invoices', function (Blueprint $table) {
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid')->change();
            $table->dropColumn([
                'receipt_uploaded_at',
                'receipt_status',
                'receipt_rejection_reason',
                'receipt_reviewed_by',
                'receipt_reviewed_at'
            ]);
        });
    }
};
