<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('announcement_submissions')->cascadeOnDelete();
            $table->string('field_name');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_submission_files');
    }
};
