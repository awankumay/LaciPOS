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
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained('products');
            $table->integer('change'); // positif = masuk, negatif = keluar
            $table->enum('reason', ['sale', 'manual_add', 'manual_reduce', 'adjustment', 'cancellation']);
            $table->ulid('reference_id')->nullable(); // order_id jika reason = sale/cancellation
            $table->string('notes')->nullable();
            $table->timestamp('created_at');

            $table->index('product_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_logs');
    }
};
