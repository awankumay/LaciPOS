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
            $table->string('printer_name')->nullable()->after('receipt_footer');
            $table->enum('paper_size', ['58mm', '80mm'])->default('80mm')->after('printer_name');
            $table->boolean('auto_print')->default(false)->after('paper_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_profiles', function (Blueprint $table) {
            $table->dropColumn(['printer_name', 'paper_size', 'auto_print']);
        });
    }
};
