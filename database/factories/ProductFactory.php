<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $products = [
            ['name' => 'Indomie Goreng', 'price' => 3500],
            ['name' => 'Indomie Kuah Soto', 'price' => 3200],
            ['name' => 'Mie Sedaap Goreng', 'price' => 3400],
            ['name' => 'Pop Mie Ayam', 'price' => 5500],
            ['name' => 'Aqua 600ml', 'price' => 4000],
            ['name' => 'Teh Botol Sosro 450ml', 'price' => 5000],
            ['name' => 'Teh Pucuk Harum 500ml', 'price' => 4500],
            ['name' => 'Susu Ultra 250ml', 'price' => 6500],
            ['name' => 'Susu Bear Brand', 'price' => 11000],
            ['name' => 'Beras Premium 5kg', 'price' => 75000],
            ['name' => 'Minyak Goreng 1L', 'price' => 18000],
            ['name' => 'Gula Pasir 1kg', 'price' => 15000],
            ['name' => 'Telur Ayam 1kg', 'price' => 30000],
            ['name' => 'Chitato Original 68g', 'price' => 12000],
            ['name' => 'Oreo Original 137g', 'price' => 12000],
            ['name' => 'Roti Tawar', 'price' => 15000],
            ['name' => 'Sabun Lifebuoy 80g', 'price' => 4500],
            ['name' => 'Shampoo Sunsilk 170ml', 'price' => 22000],
            ['name' => 'Pasta Gigi Pepsodent 190g', 'price' => 14000],
            ['name' => 'Sunlight 755ml', 'price' => 17000],
            ['name' => 'Kopi Kapal Api', 'price' => 2500],
            ['name' => 'Tissue Paseo 250 sheets', 'price' => 6500],
        ];

        $product = fake()->randomElement($products);

        return [
            'name' => $product['name'],
            'price' => $product['price'],
            'purchase_price' => (int) ($product['price'] * 0.7),
            'stock' => fake()->numberBetween(10, 100),
            'barcode' => fake()->unique()->ean13(),
            'image' => null,
        ];
    }

    /**
     * Indicate that the product has low stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => fake()->numberBetween(1, 5),
        ]);
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    /**
     * Indicate that the product has high stock.
     */
    public function highStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => fake()->numberBetween(100, 500),
        ]);
    }

    /**
     * Indicate that the product is cheap (price < 5000).
     */
    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => fake()->numberBetween(1000, 5000),
            'purchase_price' => fake()->numberBetween(500, 3500),
        ]);
    }

    /**
     * Indicate that the product is expensive (price > 50000).
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => fake()->numberBetween(50000, 150000),
            'purchase_price' => fake()->numberBetween(35000, 105000),
        ]);
    }
}
