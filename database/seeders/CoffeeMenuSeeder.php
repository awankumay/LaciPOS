<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CoffeeMenuSeeder extends Seeder
{
    /**
     * Seed 50 menu Coffee Shop: 30 minuman (varian Reguler/Medium/Large)
     * dan 20 makanan (varian Reguler/Hot). Idempotent — aman dijalankan ulang.
     */
    public function run(): void
    {
        $minuman = Category::firstOrCreate(['name' => 'Minuman']);
        $makanan = Category::firstOrCreate(['name' => 'Makanan']);

        $this->seedProducts($minuman, $this->minumanMenu(), 'Ukuran', [
            'Reguler' => 0,
            'Medium' => 3000,
            'Large' => 6000,
        ]);

        $this->seedProducts($makanan, $this->makananMenu(), 'Level', [
            'Reguler' => 0,
            'Hot' => 0,
        ]);
    }

    /**
     * @param array<string, int> $items nama => harga reguler
     * @param array<string, int> $variantOptions label => price_modifier
     */
    private function seedProducts(Category $category, array $items, string $variantGroupName, array $variantOptions): void
    {
        foreach ($items as $name => $price) {
            $product = Product::firstOrCreate(
                ['name' => $name, 'category_id' => $category->id],
                [
                    'cogs' => round($price * 0.45),
                    'price' => $price,
                    'stock' => 100,
                ]
            );

            if ($product->variants()->exists()) {
                continue;
            }

            $variant = $product->variants()->create(['name' => $variantGroupName]);

            foreach ($variantOptions as $label => $modifier) {
                $variant->options()->create([
                    'label' => $label,
                    'price_modifier' => $modifier,
                    'cogs_modifier' => 0,
                ]);
            }
        }
    }

    /** @return array<string, int> */
    private function minumanMenu(): array
    {
        return [
            'Americano' => 18000,
            'Espresso' => 16000,
            'Cappuccino' => 22000,
            'Caffe Latte' => 22000,
            'Caramel Macchiato' => 26000,
            'Hazelnut Latte' => 25000,
            'Vanilla Latte' => 25000,
            'Butterscotch Latte' => 26000,
            'Mocha' => 24000,
            'Flat White' => 23000,
            'Affogato' => 28000,
            'Vietnam Drip' => 20000,
            'Kopi Tubruk' => 15000,
            'Kopi Hitam' => 14000,
            'Es Kopi Susu' => 20000,
            'Kopi Susu Gula Aren' => 22000,
            'Kopi Susu Original' => 19000,
            'Cold Brew' => 21000,
            'Es Kopi Pandan' => 22000,
            'Matcha Latte' => 24000,
            'Red Velvet Latte' => 25000,
            'Taro Latte' => 25000,
            'Chocolate Latte' => 23000,
            'Thai Tea' => 20000,
            'Lemon Tea' => 16000,
            'Lychee Tea' => 18000,
            'Es Teh Manis' => 10000,
            'Strawberry Milk' => 20000,
            'Oreo Milkshake' => 26000,
            'Vanilla Milkshake' => 24000,
        ];
    }

    /** @return array<string, int> */
    private function makananMenu(): array
    {
        return [
            'Croissant' => 18000,
            'Garlic Bread' => 20000,
            'French Fries' => 17000,
            'Beef Burger' => 32000,
            'Chicken Burger' => 28000,
            'Spaghetti Carbonara' => 30000,
            'Spaghetti Bolognese' => 30000,
            'Nasi Goreng Spesial' => 25000,
            'Chicken Wings' => 28000,
            'Club Sandwich' => 27000,
            'Donat' => 10000,
            'Waffle' => 22000,
            'Pancake' => 22000,
            'Roti Bakar Coklat' => 15000,
            'Roti Bakar Keju' => 16000,
            'Chicken Nugget' => 20000,
            'Onion Rings' => 18000,
            'Cheese Stick' => 15000,
            'Pisang Goreng' => 12000,
            'Perkedel Kentang' => 15000,
        ];
    }
}
