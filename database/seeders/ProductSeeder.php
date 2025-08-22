<?php
// database/seeders/ProductSeeder.php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            // Hot Drinks
            [
                'name' => 'Coffee Americano',
                'description' => 'Strong black coffee',
                'base_price' => 25.00,
                'category' => 'Hot Beverages',
                'temperature_preference' => 'hot'
            ],
            [
                'name' => 'Cappuccino',
                'description' => 'Coffee with steamed milk foam',
                'base_price' => 30.00,
                'category' => 'Hot Beverages',
                'temperature_preference' => 'hot'
            ],
            [
                'name' => 'Hot Chocolate',
                'description' => 'Rich chocolate drink',
                'base_price' => 35.00,
                'category' => 'Hot Beverages',
                'temperature_preference' => 'hot'
            ],
            [
                'name' => 'Green Tea',
                'description' => 'Healthy green tea',
                'base_price' => 20.00,
                'category' => 'Hot Beverages',
                'temperature_preference' => 'hot'
            ],

            // Cold Drinks
            [
                'name' => 'Iced Coffee',
                'description' => 'Cold brew coffee with ice',
                'base_price' => 28.00,
                'category' => 'Cold Beverages',
                'temperature_preference' => 'cold'
            ],
            [
                'name' => 'Fresh Orange Juice',
                'description' => '100% natural orange juice',
                'base_price' => 32.00,
                'category' => 'Cold Beverages',
                'temperature_preference' => 'cold'
            ],
            [
                'name' => 'Lemonade',
                'description' => 'Fresh lemon drink',
                'base_price' => 25.00,
                'category' => 'Cold Beverages',
                'temperature_preference' => 'cold'
            ],
            [
                'name' => 'Smoothie',
                'description' => 'Mixed fruit smoothie',
                'base_price' => 40.00,
                'category' => 'Cold Beverages',
                'temperature_preference' => 'cold'
            ],

            // Neutral items
            [
                'name' => 'Croissant',
                'description' => 'Buttery pastry',
                'base_price' => 15.00,
                'category' => 'Bakery',
                'temperature_preference' => 'neutral'
            ],
            [
                'name' => 'Sandwich',
                'description' => 'Fresh sandwich',
                'base_price' => 45.00,
                'category' => 'Food',
                'temperature_preference' => 'neutral'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}