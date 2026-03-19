<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE foreign_companies MODIFY COLUMN status ENUM('uploading_documents', 'pending', 'approved', 'payment_review', 'active', 'rejected', 'suspended', 'pending_payment', 'expired') DEFAULT 'uploading_documents'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE foreign_companies MODIFY COLUMN status ENUM('uploading_documents', 'pending', 'approved', 'active', 'rejected', 'suspended', 'pending_payment', 'expired') DEFAULT 'uploading_documents'");
    }
};
