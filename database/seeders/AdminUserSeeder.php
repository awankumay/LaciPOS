<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed user owner untuk akses login. Skip kalau email sudah ada,
     * jadi tidak menimpa password yang sudah dipakai.
     * Password digenerate random dan ditampilkan sekali di console
     * (tidak pernah di-hardcode / disimpan di kode).
     */
    public function run(): void
    {
        $existing = User::where('email', 'admin@example.com')->first();

        if ($existing) {
            $existing->forceFill(['role' => 'owner', 'is_active' => true])->save();
            return;
        }

        $password = Str::random(16);

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => $password,
        ]);

        $user->forceFill([
            'role' => 'owner',
            'is_active' => true,
        ])->save();

        $this->command?->warn("Admin user dibuat — Email: admin@example.com | Password: {$password}");
    }
}
