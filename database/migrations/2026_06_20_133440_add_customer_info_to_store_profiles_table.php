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
        Schema::table('store_profiles', function (Blueprint $table) {
            $table->boolean('enable_customer_name')->default(false)->after('auto_print');
            $table->boolean('enable_table_number')->default(false)->after('enable_customer_name');
            $table->boolean('enable_order_notes')->default(false)->after('enable_table_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_profiles', function (Blueprint $table) {
            //
        });
    }
};
