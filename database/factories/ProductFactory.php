<?php
namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $cogs = fake()->numberBetween(5000, 50000);
        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(2, true),
            'cogs' => $cogs,
            'price' => $cogs * fake()->randomFloat(1, 1.3, 2.5),
            'stock' => fake()->numberBetween(0, 100),
            'min_stock_alert' => 5,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function lowStock(): static
    {
        return $this->state(fn () => ['stock' => 2, 'min_stock_alert' => 5]);
    }
}
