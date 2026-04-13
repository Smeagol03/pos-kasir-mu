<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionItem>
 */
class TransactionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $quantity = fake()->numberBetween(1, 5);
        $price = $product->price;
        $purchasePrice = $product->purchase_price;
        $subtotal = $quantity * $price;

        return [
            'transaction_id' => Transaction::factory(),
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $price,
            'purchase_price' => $purchasePrice,
            'subtotal' => $subtotal,
        ];
    }

    /**
     * Indicate that the item has a specific product.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'price' => $product->price,
            'purchase_price' => $product->purchase_price,
            'subtotal' => $attributes['quantity'] * $product->price,
        ]);
    }

    /**
     * Indicate that the item has a specific transaction.
     */
    public function forTransaction(Transaction $transaction): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_id' => $transaction->id,
        ]);
    }

    /**
     * Indicate that the item quantity is high (> 10).
     */
    public function highQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(10, 20),
        ]);
    }

    /**
     * Indicate that the item is a single purchase.
     */
    public function single(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 1,
        ]);
    }
}
