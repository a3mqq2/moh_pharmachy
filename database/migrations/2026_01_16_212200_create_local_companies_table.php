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
        Schema::create('local_companies', function (Blueprint $table) {
            $table->id();

            $table->string('company_name');
            $table->text('company_address')->nullable();
            $table->string('street')->nullable();
            $table->string('city');
            $table->string('phone');
            $table->string('mobile')->nullable();
            $table->string('email')->unique();

            $table->date('registration_date')->nullable();
            $table->string('registration_number')->nullable()->unique();

            $table->enum('license_type', ['company', 'partnership', 'authorized_agent'])->default('company');
            $table->enum('license_specialty', ['medicines', 'medical_supplies', 'medical_equipment'])->default('medicines');

            $table->string('license_number')->nullable();
            $table->string('license_issuer')->nullable();
            $table->string('food_drug_registration_number')->nullable();
            $table->string('chamber_of_commerce_number')->nullable();

            $table->string('manager_name');
            $table->string('manager_position')->nullable();
            $table->string('manager_phone');
            $table->string('manager_email')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending');
            $table->text('rejection_reason')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('local_companies');
    }
};
