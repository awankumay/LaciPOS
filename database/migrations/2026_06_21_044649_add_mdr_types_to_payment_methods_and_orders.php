<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update payment_methods
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('admin_fee_type')->default('percentage')->after('account_details');
            $table->decimal('admin_fee', 15, 2)->default(0)->after('admin_fee_type');
        });

        // Migrate existing data
        DB::statement('UPDATE payment_methods SET admin_fee = admin_fee_percentage');

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('admin_fee_percentage');
        });

        // Update orders
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_admin_fee_type')->default('percentage')->after('payment_method');
            $table->decimal('payment_admin_fee', 15, 2)->default(0)->after('payment_admin_fee_type');
        });

        // Migrate existing data
        DB::statement('UPDATE orders SET payment_admin_fee = payment_admin_fee_rate');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_admin_fee_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->decimal('admin_fee_percentage', 5, 2)->default(0);
        });

        DB::statement('UPDATE payment_methods SET admin_fee_percentage = admin_fee');

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['admin_fee_type', 'admin_fee']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('payment_admin_fee_rate', 5, 2)->default(0);
        });

        DB::statement('UPDATE orders SET payment_admin_fee_rate = payment_admin_fee');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_admin_fee_type', 'payment_admin_fee']);
        });
    }
};
