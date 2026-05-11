<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('name', 'idx_products_name');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index('invoice_number', 'idx_transactions_invoice');
        });

        if (config('database.default') === 'mysql') {
            Schema::table('products', function (Blueprint $table) {
                $table->fullText(['name', 'sku'], 'idx_products_fulltext');
            });

            Schema::table('transactions', function (Blueprint $table) {
                $table->fullText(['invoice_number'], 'idx_transactions_fulltext');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_name');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_invoice');
        });

        if (config('database.default') === 'mysql') {
            Schema::table('products', function (Blueprint $table) {
                $table->dropFullText('idx_products_fulltext');
            });

            Schema::table('transactions', function (Blueprint $table) {
                $table->dropFullText('idx_transactions_fulltext');
            });
        }
    }
};
