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
        Schema::create('pharmaceutical_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foreign_company_id')->constrained()->onDelete('cascade');
            $table->foreignId('representative_id')->constrained('users')->onDelete('cascade');
            $table->string('product_name');
            $table->string('pharmaceutical_form');
            $table->string('concentration');
            $table->json('usage_methods');
            $table->string('other_usage_method')->nullable();
            $table->enum('status', [
                'uploading_documents',
                'pending_review',
                'approved',
                'rejected',
                'active',
                'suspended'
            ])->default('uploading_documents');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmaceutical_products');
    }
};
