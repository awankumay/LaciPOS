<?php

namespace Database\Seeders;

use App\Models\StoreProfile;
use Illuminate\Database\Seeder;

class StoreProfileSeeder extends Seeder
{
    /**
     * Seed profil toko hasil setup onboarding. Hanya ada 1 record,
     * jadi kalau sudah pernah onboarding, seeder ini di-skip.
     */
    public function run(): void
    {
        if (StoreProfile::getProfile()) {
            return;
        }

        StoreProfile::create([
            'store_name' => 'MITRA COFFE',
            'timezone' => 'Asia/Makassar',
            'address' => 'Komp. Pelangi Jaya Lestari G3, Kec. Cempaka',
            'phone' => '08123228570',
            // logo_path tidak diikutkan — file fisiknya tidak ikut diseed.
            'receipt_footer' => 'Terima kasih sudah berbelanja di toko kami!',
            'paper_size' => '80mm',
            'auto_print' => false,
            'default_min_stock' => 5,
            'tax_enabled' => false,
            'tax_type' => 'percentage',
            'tax_value' => 0,
            'service_charge_enabled' => false,
            'service_charge_type' => 'percentage',
            'service_charge_value' => 0,
            'enable_customer_name' => false,
            'enable_table_number' => false,
            'enable_order_notes' => false,
        ]);
    }
}
