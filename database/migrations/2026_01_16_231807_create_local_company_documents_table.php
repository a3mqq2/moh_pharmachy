<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('local_company_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('local_company_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->string('custom_name')->nullable();
            $table->string('original_name');
            $table->string('file_path');
            $table->string('file_extension');
            $table->unsignedBigInteger('file_size');
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_company_documents');
    }
};
