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
        Schema::create('pharmaceutical_product_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pharmaceutical_product_id');
            $table->enum('document_type', [
                'registration_forms',
                'fda_certificate',
                'ema_certificate',
                'cpp_fsc_certificate',
                'pricing_certificate',
                'other_countries_registration',
                'drug_master_file',
                'product_specifications',
                'active_ingredients_analysis',
                'packaging_specifications',
                'accelerated_stability_studies',
                'hot_climate_stability_studies',
                'pharmacology_toxicology_studies',
                'bioequivalence_studies',
                'product_labels',
                'internal_leaflets',
            ]);
            $table->string('file_path');
            $table->string('original_name');
            $table->unsignedBigInteger('file_size');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pharmaceutical_product_id', 'pp_docs_product_fk')
                  ->references('id')
                  ->on('pharmaceutical_products')
                  ->onDelete('cascade');

            $table->foreign('uploaded_by', 'pp_docs_user_fk')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmaceutical_product_documents');
    }
};
