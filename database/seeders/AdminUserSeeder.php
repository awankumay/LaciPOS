<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed user owner untuk akses login. Skip kalau email sudah ada,
     * jadi tidak menimpa password yang sudah dipakai.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => 'password123',
            ]
        );

        $user->forceFill([
            'role' => 'owner',
            'is_active' => true,
        ])->save();
    }
}
