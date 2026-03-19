<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_documents', function (Blueprint $table) {
            $table->string('visibility')->default('all')->after('notes');
            $table->foreignId('department_id')->nullable()->after('visibility')->constrained('departments')->nullOnDelete();
        });

        Schema::create('admin_document_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_document_id')->constrained('admin_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['admin_document_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_document_users');

        Schema::table('admin_documents', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['visibility', 'department_id']);
        });
    }
};
