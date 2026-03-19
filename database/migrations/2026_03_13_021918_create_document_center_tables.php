<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('file_extension', 20)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('shared_files', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('file_extension', 20)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('shared_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('shared_file_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shared_file_id')->constrained('shared_files')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_file_users');
        Schema::dropIfExists('shared_files');
        Schema::dropIfExists('admin_documents');
    }
};
