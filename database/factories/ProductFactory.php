<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'base_price' => $this->faker->randomFloat(2, 10, 100),
            'category' => $this->faker->randomElement(['Hot Drinks', 'Cold Drinks', 'Snacks']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}