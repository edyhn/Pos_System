<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['type', 'store_id', 'created_at'], 'stock_movements_type_store_date_idx');
            $table->index(['product_id', 'type'], 'stock_movements_product_type_idx');
            $table->index(['reference_type', 'reference_id'], 'stock_movements_reference_idx');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('stock_movements_type_store_date_idx');
            $table->dropIndex('stock_movements_product_type_idx');
            $table->dropIndex('stock_movements_reference_idx');
        });
    }
};
