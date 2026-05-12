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
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->decimal('discount_amount', 15, 2)->default(0)->after('subtotal');
            $table->string('discount_name')->nullable()->after('discount_amount');
            $table->foreignId('discount_id')->nullable()->constrained()->nullOnDelete()->after('discount_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_id');
            $table->dropColumn(['discount_amount', 'discount_name']);
        });
    }
};
