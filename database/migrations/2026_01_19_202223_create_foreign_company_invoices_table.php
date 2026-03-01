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
        Schema::create('foreign_company_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foreign_company_id')->constrained('foreign_companies')->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();

            // Invoice status
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');

            // Receipt information
            $table->string('receipt_path')->nullable();
            $table->timestamp('receipt_uploaded_at')->nullable();
            $table->enum('receipt_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->text('receipt_rejection_reason')->nullable();
            $table->foreignId('receipt_reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('receipt_reviewed_at')->nullable();

            // Payment information
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foreign_company_invoices');
    }
};
