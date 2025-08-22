<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Product;
use App\Events\NewOrderCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RealTimeOrderTest extends TestCase
{
    
    use RefreshDatabase;
    /** @test */
    public function it_dispatches_event_when_new_order_is_created()
    {
        Event::fake();
    
        $product = Product::factory()->create([
            'name' => 'Test Coffee',
            'base_price' => 25.00,
            'category' => 'Hot Drinks'
        ]);

        $orderData = [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 25.00,
            'total_amount' => 50.00,
            'final_amount' => 50.00,
            'order_date' => now()->toDateTimeString()
        ];

        $response = $this->postJson('api/v1/orders', $orderData);

        $response->assertStatus(201);
        Event::assertDispatched(NewOrderCreated::class);
    }


}