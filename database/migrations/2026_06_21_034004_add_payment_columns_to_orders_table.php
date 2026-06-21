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
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('payment_method_name')->nullable();
            $table->string('payment_account_details')->nullable();
            $table->decimal('payment_admin_fee_rate', 5, 2)->default(0);
            $table->decimal('payment_admin_fee_amount', 15, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn([
                'payment_method_id',
                'payment_method_name',
                'payment_account_details',
                'payment_admin_fee_rate',
                'payment_admin_fee_amount',
            ]);
        });
    }
};
