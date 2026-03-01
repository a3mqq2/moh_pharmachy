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
        DB::statement("ALTER TABLE pharmaceutical_products MODIFY COLUMN status ENUM('uploading_documents', 'pending_review', 'preliminary_approved', 'pending_final_approval', 'pending_payment', 'rejected', 'active') NOT NULL DEFAULT 'uploading_documents'");

        Schema::table('pharmaceutical_products', function (Blueprint $table) {
            $table->string('trade_name')->nullable()->after('product_name');
            $table->string('origin')->nullable()->after('trade_name');
            $table->string('unit')->nullable()->after('origin');
            $table->string('packaging')->nullable()->after('unit');
            $table->integer('quantity')->nullable()->after('packaging');
            $table->decimal('unit_price', 10, 2)->nullable()->after('quantity');
            $table->integer('shelf_life_months')->nullable()->after('unit_price');
            $table->string('storage_conditions')->nullable()->after('shelf_life_months');
            $table->string('free_sale')->nullable()->after('storage_conditions');
            $table->string('samples')->nullable()->after('free_sale');
            $table->string('pharmacopeal_ref')->nullable()->after('samples');
            $table->string('item_classification')->nullable()->after('pharmacopeal_ref');
            $table->timestamp('preliminary_approved_at')->nullable()->after('reviewed_at');
            $table->foreignId('preliminary_approved_by')->nullable()->after('preliminary_approved_at')->constrained('users')->onDelete('set null');
            $table->timestamp('final_approved_at')->nullable()->after('preliminary_approved_by');
            $table->foreignId('final_approved_by')->nullable()->after('final_approved_at')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmaceutical_products', function (Blueprint $table) {
            $table->dropColumn([
                'trade_name',
                'origin',
                'unit',
                'packaging',
                'quantity',
                'unit_price',
                'shelf_life_months',
                'storage_conditions',
                'free_sale',
                'samples',
                'pharmacopeal_ref',
                'item_classification',
                'preliminary_approved_at',
                'preliminary_approved_by',
                'final_approved_at',
                'final_approved_by',
            ]);
        });

        DB::statement("ALTER TABLE pharmaceutical_products MODIFY COLUMN status ENUM('uploading_documents', 'pending_review', 'approved', 'pending_payment', 'rejected', 'active') NOT NULL DEFAULT 'uploading_documents'");
    }
};
