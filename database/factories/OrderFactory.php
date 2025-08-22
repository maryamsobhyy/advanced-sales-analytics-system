<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 5),
            'unit_price' => $this->faker->randomFloat(2, 10, 50),
            'total_amount' => function (array $attributes) {
                return $attributes['quantity'] * $attributes['unit_price'];
            },
            'final_amount' => function (array $attributes) {
                return $attributes['quantity'] * $attributes['unit_price'];
            },
            'order_date' => $this->faker->dateTimeThisMonth(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}