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
            $table->decimal('subtotal', 15, 2)->default(0)->after('total_amount');
            
            $table->decimal('tax_rate', 15, 2)->default(0)->after('subtotal');
            $table->string('tax_type', 20)->nullable()->after('tax_rate');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_type');
            
            $table->decimal('service_charge_rate', 15, 2)->default(0)->after('tax_amount');
            $table->string('service_charge_type', 20)->nullable()->after('service_charge_rate');
            $table->decimal('service_charge_amount', 15, 2)->default(0)->after('service_charge_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'tax_rate',
                'tax_type',
                'tax_amount',
                'service_charge_rate',
                'service_charge_type',
                'service_charge_amount',
            ]);
        });
    }
};
