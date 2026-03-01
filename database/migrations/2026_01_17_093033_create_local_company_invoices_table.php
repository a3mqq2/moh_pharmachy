<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('local_company_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('local_company_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->enum('type', ['registration', 'renewal', 'other'])->default('registration');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->date('due_date')->nullable();
            $table->date('paid_at')->nullable();
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_company_invoices');
    }
};
