<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('local_companies', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index('representative_id');
        });

        Schema::table('foreign_companies', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index('representative_id');
            $table->index('local_company_id');
        });

        Schema::table('pharmaceutical_products', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index('representative_id');
            $table->index('foreign_company_id');
        });

        Schema::table('local_company_invoices', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index('local_company_id');
        });

        Schema::table('pharmaceutical_product_invoices', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index('pharmaceutical_product_id');
        });

        if (Schema::hasTable('foreign_company_invoices')) {
            Schema::table('foreign_company_invoices', function (Blueprint $table) {
                $table->index(['status', 'created_at']);
                $table->index('foreign_company_id');
            });
        }

        if (Schema::hasTable('announcements')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->index(['priority', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('local_companies', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['representative_id']);
        });

        Schema::table('foreign_companies', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['representative_id']);
            $table->dropIndex(['local_company_id']);
        });

        Schema::table('pharmaceutical_products', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['representative_id']);
            $table->dropIndex(['foreign_company_id']);
        });

        Schema::table('local_company_invoices', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['local_company_id']);
        });

        Schema::table('pharmaceutical_product_invoices', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['pharmaceutical_product_id']);
        });

        if (Schema::hasTable('foreign_company_invoices')) {
            Schema::table('foreign_company_invoices', function (Blueprint $table) {
                $table->dropIndex(['status', 'created_at']);
                $table->dropIndex(['foreign_company_id']);
            });
        }

        if (Schema::hasTable('announcements')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropIndex(['priority', 'created_at']);
            });
        }
    }
};
