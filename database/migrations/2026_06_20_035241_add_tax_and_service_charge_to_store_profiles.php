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
            $table->boolean('tax_enabled')->default(false)->after('auto_print');
            $table->string('tax_type', 20)->default('percentage')->after('tax_enabled'); // percentage or nominal
            $table->decimal('tax_value', 15, 2)->default(0)->after('tax_type');
            
            $table->boolean('service_charge_enabled')->default(false)->after('tax_value');
            $table->string('service_charge_type', 20)->default('percentage')->after('service_charge_enabled');
            $table->decimal('service_charge_value', 15, 2)->default(0)->after('service_charge_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'tax_enabled',
                'tax_type',
                'tax_value',
                'service_charge_enabled',
                'service_charge_type',
                'service_charge_value',
            ]);
        });
    }
};
