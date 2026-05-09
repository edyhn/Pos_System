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
        Schema::table('products', function (Blueprint $table) {
            $table->index(['store_id', 'is_active', 'name'], 'idx_products_store_active_name');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index(['store_id', 'name'], 'idx_categories_store_name');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['store_id', 'created_at', 'status'], 'idx_transactions_store_date_status');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['store_id', 'type', 'created_at'], 'idx_stock_movements_store_type_date');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['store_id', 'role', 'is_active'], 'idx_users_store_role_active');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index(['store_id', 'status', 'created_at'], 'idx_po_store_status_date');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['store_id', 'status', 'end_date'], 'idx_subs_store_status_end');
        });

        Schema::table('refund_requests', function (Blueprint $table) {
            $table->index(['store_id', 'status', 'created_at'], 'idx_refund_store_status_date');
        });

        Schema::table('receipt_reprint_requests', function (Blueprint $table) {
            $table->index(['store_id', 'status', 'created_at'], 'idx_reprint_store_status_date');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn(Blueprint $t) => $t->dropIndex('idx_products_store_active_name'));
        Schema::table('categories', fn(Blueprint $t) => $t->dropIndex('idx_categories_store_name'));
        Schema::table('transactions', fn(Blueprint $t) => $t->dropIndex('idx_transactions_store_date_status'));
        Schema::table('stock_movements', fn(Blueprint $t) => $t->dropIndex('idx_stock_movements_store_type_date'));
        Schema::table('users', fn(Blueprint $t) => $t->dropIndex('idx_users_store_role_active'));
        Schema::table('purchase_orders', fn(Blueprint $t) => $t->dropIndex('idx_po_store_status_date'));
        Schema::table('subscriptions', fn(Blueprint $t) => $t->dropIndex('idx_subs_store_status_end'));
        Schema::table('refund_requests', fn(Blueprint $t) => $t->dropIndex('idx_refund_store_status_date'));
        Schema::table('receipt_reprint_requests', fn(Blueprint $t) => $t->dropIndex('idx_reprint_store_status_date'));
    }
};
