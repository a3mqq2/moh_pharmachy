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
        Schema::create('foreign_company_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foreign_company_id')->constrained('foreign_companies')->onDelete('cascade');

            // Document type with all required documents
            $table->enum('document_type', [
                'official_registration_request',     // طلب تسجيل رسمي من الشركة المصنعة
                'agency_agreement',                  // رسالة وكالة أو اتفاقية توزيع
                'registration_forms',                // نماذج التسجيل المعتمدة
                'gmp_certificate',                   // شهادة GMP
                'fda_certificate',                   // شهادة FDA
                'emea_certificate',                  // شهادة EMEA
                'cpp_certificate',                   // شهادة المنتج الصيدلاني
                'fsc_certificate',                   // شهادة البيع الحر
                'manufacturing_license',             // ترخيص تصنيع ساري
                'financial_report',                  // تقرير مالي لآخر سنتين
                'products_list',                     // قائمة منتجات الشركة
                'site_master_file',                  // الملف الرئيسي للمصنع
                'registration_certificates',        // شهادات تسجيل في دول أخرى
                'exclusive_agency_contract',         // عقد الوكالة الحصري
                'other'                              // مستندات أخرى
            ]);

            $table->string('document_name');
            $table->string('file_path');
            $table->string('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->text('notes')->nullable();

            // Document status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
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
        Schema::dropIfExists('foreign_company_documents');
    }
};
