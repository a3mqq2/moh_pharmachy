<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE pharmaceutical_products MODIFY COLUMN status ENUM('uploading_documents', 'pending_review', 'preliminary_approved', 'pending_final_approval', 'pending_payment', 'payment_review', 'rejected', 'active') NOT NULL DEFAULT 'uploading_documents'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE pharmaceutical_products MODIFY COLUMN status ENUM('uploading_documents', 'pending_review', 'preliminary_approved', 'pending_final_approval', 'pending_payment', 'rejected', 'active') NOT NULL DEFAULT 'uploading_documents'");
    }
};
