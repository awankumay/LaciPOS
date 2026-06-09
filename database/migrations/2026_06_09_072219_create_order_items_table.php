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
        Schema::create('order_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained('products');
            $table->string('product_name_snapshot');
            $table->decimal('snapshot_cogs', 12, 2);
            $table->decimal('snapshot_price', 12, 2);
            $table->string('variant_label')->nullable();
            $table->integer('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->string('notes')->nullable();

            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
