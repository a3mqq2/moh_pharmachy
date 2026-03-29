<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('representative_id')->constrained('company_representatives')->cascadeOnDelete();
            $table->json('data')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            $table->unique(['announcement_id', 'representative_id'], 'ann_sub_ann_rep_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_submissions');
    }
};
