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
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('discount_type', ['percentage', 'nominal'])->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('discount_note')->nullable();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->enum('snapshot_discount_type', ['percentage', 'nominal'])->nullable();
            $table->decimal('snapshot_discount_value', 12, 2)->nullable();
            $table->decimal('snapshot_discount_amount', 12, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type',
                'discount_value',
                'discount_amount',
                'discount_note',
            ]);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'snapshot_discount_type',
                'snapshot_discount_value',
                'snapshot_discount_amount',
            ]);
        });
    }
};
