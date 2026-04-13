<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->numberBetween(10000, 500000);
        $cash = $total + fake()->numberBetween(0, 50000);

        return [
            'invoice_code' => 'INV-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
            'user_id' => User::factory(),
            'total' => $total,
            'cash' => $cash,
            'change' => $cash - $total,
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the transaction was made by an admin.
     */
    public function byAdmin(): static
    {
        return $this->for(User::factory()->admin(), 'user');
    }

    /**
     * Indicate that the transaction was made by a kasir.
     */
    public function byKasir(): static
    {
        return $this->for(User::factory()->kasir(), 'user');
    }

    /**
     * Indicate that the transaction was made on a specific date.
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $date,
        ]);
    }

    /**
     * Indicate that the transaction was made today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now(),
        ]);
    }

    /**
     * Indicate that the transaction was made yesterday.
     */
    public function yesterday(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the transaction is cheap (< 50000).
     */
    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'total' => fake()->numberBetween(5000, 50000),
        ]);
    }

    /**
     * Indicate that the transaction is expensive (> 200000).
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'total' => fake()->numberBetween(200000, 1000000),
        ]);
    }
}
