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
        Schema::create('foreign_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('representative_id')->constrained('company_representatives')->onDelete('cascade');
            $table->foreignId('local_company_id')->constrained('local_companies')->onDelete('cascade');

            // Basic Information
            $table->string('company_name');
            $table->string('country');
            $table->enum('entity_type', ['company', 'factory']); // شركة أو مصنع
            $table->text('address');
            $table->string('email');

            // Activity Information
            $table->enum('activity_type', ['medicines', 'medical_supplies', 'both']); // أدوية - مستلزمات طبية - كلاهما
            $table->integer('products_count')->default(0); // عدد المنتجات المراد تسجيلها
            $table->json('registered_countries')->nullable(); // قائمة الدول المسجَّلة بها

            // Status and workflow
            $table->enum('status', [
                'uploading_documents', // قيد رفع المستندات
                'pending',             // قيد المراجعة
                'pending_payment',     // قيد السداد
                'approved',            // مقبولة
                'active',              // مفعلة
                'rejected',            // مرفوضة
                'suspended'            // معلقة
            ])->default('uploading_documents');

            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('activated_at')->nullable();
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
        Schema::dropIfExists('foreign_companies');
    }
};
