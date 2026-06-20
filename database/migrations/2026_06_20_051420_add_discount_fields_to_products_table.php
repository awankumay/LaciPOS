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
            $table->enum('discount_type', ['percentage', 'nominal'])->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->date('discount_start_date')->nullable();
            $table->date('discount_end_date')->nullable();
            $table->time('discount_start_time')->nullable();
            $table->time('discount_end_time')->nullable();
            $table->integer('discount_quota')->nullable();
            $table->integer('discount_quota_used')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type',
                'discount_value',
                'discount_start_date',
                'discount_end_date',
                'discount_start_time',
                'discount_end_time',
                'discount_quota',
                'discount_quota_used',
            ]);
        });
    }
};
