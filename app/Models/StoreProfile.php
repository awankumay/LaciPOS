<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class StoreProfile extends Model
{
    use HasUlids;

    protected $fillable = [
        'store_name',
        'address',
        'phone',
        'logo_path',
        'receipt_footer',
    ];

    /**
     * Ambil profil toko (hanya 1 record).
     * Jika belum ada, return null.
     */
    public static function getProfile(): ?self
    {
        return static::first();
    }

    /**
     * Cek apakah onboarding (setup toko) sudah selesai.
     * Onboarding dianggap selesai jika store_name sudah diisi.
     */
    public static function isOnboardingCompleted(): bool
    {
        $profile = static::first();
        return $profile !== null && !empty($profile->store_name);
    }

    /**
     * Dapatkan URL logo toko.
     * Return null jika logo belum diupload.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        return asset('storage/' . $this->logo_path);
    }
}
