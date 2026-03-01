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
        Schema::create('pharmaceutical_product_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmaceutical_product_id')->constrained('pharmaceutical_products')->onDelete('cascade')->name('pharma_product_invoices_product_id_fk');
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['unpaid', 'pending_review', 'paid'])->default('unpaid');
            $table->string('receipt_path')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmaceutical_product_invoices');
    }
};
