<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('security_question')->nullable()->after('is_active');
            $table->string('security_answer')->nullable()->after('security_question');
            $table->json('recovery_codes')->nullable()->after('security_answer');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['security_question', 'security_answer', 'recovery_codes']);
        });
    }
};
