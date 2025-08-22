<?php
// app/Http/Controllers/OrderController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Events\NewOrderCreated;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Foundation\Services\WeatherService;
use App\Http\Requests\Orders\CreateOrderRequest;
use App\Foundation\Repositories\Interfaces\OrderRepositoryInterface;

class OrderController extends Controller
{
    private OrderRepositoryInterface $orderRepository;
    private WeatherService $weatherService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        WeatherService $weatherService
    ) {
        $this->orderRepository = $orderRepository;
        $this->weatherService = $weatherService;
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Get product
            $product = Product::findOrFail($request->product_id);
            
            // Get current weather
            $weatherData = $this->weatherService->getCurrentWeather($request->city);
            
            // Calculate pricing
            $quantity = $request->quantity;
            $unitPrice = $request->price ?? $product->base_price;
            $totalAmount = $unitPrice * $quantity;
            
            // Create temporary order to calculate discount
            $tempOrder = new \App\Models\Order();
            $tempOrder->product_id = $product->id;
            $tempOrder->setRelation('product', $product);
            
            $weatherDiscount = $tempOrder->calculateDiscount(
                $weatherData['temperature'],
                $weatherData['condition']
            );
            
            $finalAmount = $totalAmount * (1 - ($weatherDiscount / 100));
            
            // Create order
            $orderData = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'weather_discount' => $weatherDiscount,
                'final_amount' => $finalAmount,
                'weather_data' => $weatherData,
                'order_date' => $request->date
            ];
            
            $order = $this->orderRepository->create($orderData);
            $order->load('product:id,name,category,temperature_preference');
            
            DB::commit();
            
            // Fire event for real-time updates
            event(new NewOrderCreated($order));
            
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully.',
                'data' => [
                    'order' => $order,
                    'weather_info' => [
                        'temperature' => $weatherData['temperature'],
                        'condition' => $weatherData['condition'],
                        'city' => $weatherData['city'],
                        'discount_applied' => $weatherDiscount > 0,
                        'discount_percentage' => $weatherDiscount,
                        'savings' => $totalAmount - $finalAmount
                    ],
                    'pricing_breakdown' => [
                        'unit_price' => $unitPrice,
                        'quantity' => $quantity,
                        'subtotal' => $totalAmount,
                        'weather_discount' => "{$weatherDiscount}%",
                        'discount_amount' => $totalAmount - $finalAmount,
                        'final_total' => $finalAmount
                    ]
                ]
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Order Creation Failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}