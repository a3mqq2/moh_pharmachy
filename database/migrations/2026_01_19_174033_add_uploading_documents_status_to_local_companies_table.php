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
        // Modify the status enum to include 'uploading_documents'
        DB::statement("ALTER TABLE local_companies MODIFY COLUMN status ENUM('uploading_documents', 'pending', 'approved', 'rejected', 'suspended') DEFAULT 'uploading_documents'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original status enum
        DB::statement("ALTER TABLE local_companies MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'suspended') DEFAULT 'pending'");
    }
};
