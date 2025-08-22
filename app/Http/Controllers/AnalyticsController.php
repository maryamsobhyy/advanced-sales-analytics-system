<?php
// app/Http/Controllers/AnalyticsController.php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Foundation\Services\AIService;
use Illuminate\Support\Facades\Schema;
use App\Foundation\Services\WeatherService;
use App\Foundation\Repositories\Interfaces\OrderRepositoryInterface;

class AnalyticsController extends Controller
{
    private OrderRepositoryInterface $orderRepository;
    protected AIService $aiService;
    protected WeatherService $weatherService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        AIService $aiService,
        WeatherService $weatherService
    ) {
        $this->orderRepository = $orderRepository;
        $this->aiService = $aiService;
        $this->weatherService = $weatherService;
    }

    public function index(): JsonResponse
    {
        try {
            // Cache analytics for 30 seconds to improve performance
            $analytics = Cache::remember('sales_analytics', 30, function () {
                return $this->orderRepository->getAnalytics();
            });

            // Add real-time data (not cached)
            $realtimeData = [
                'last_minute_orders' => $this->orderRepository->getOrdersLastMinute(),
                'timestamp' => now(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'analytics' => $analytics,
                    'realtime' => $realtimeData,
                    'performance_metrics' => [
                        'cache_hit' => Cache::has('sales_analytics'),
                        'data_freshness' => 'Last updated: ' . now()->diffForHumans(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve analytics.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function realtimeStats(): JsonResponse
    {
        try {
            $lastMinuteOrders = $this->orderRepository->getOrdersLastMinute();
            $revenueChange = $this->orderRepository->getRevenueChangeLastMinute();

            return response()->json([
                'success' => true,
                'data' => [
                    'orders_last_minute' => $lastMinuteOrders->count(),
                    'revenue_change_last_minute' => $revenueChange,
                    'recent_orders' => $lastMinuteOrders->take(5), // Latest 5 orders
                    'timestamp' => now(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve realtime stats.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function getRecommendations(Request $request): JsonResponse
    {
        try {
            // Get sales data for analysis
            $salesData = $this->getSalesDataForAnalysis();

            // Get weather data
            $salesData['current_weather'] = $this->getCurrentWeatherData();

            // Generate AI recommendations
            $recommendations = $this->aiService->generateRecommendations($salesData);

            return response()->json([
                'success' => true,
                'data' => [
                    'recommendations' => $recommendations,
                    'analysis_data' => $salesData,
                    'generated_at' => now()->toDateTimeString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate recommendations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prepare sales data for AI analysis
     */
    protected function getSalesDataForAnalysis(): array
    {
        $oneHourAgo = Carbon::now()->subHour();

        return [
            'total_revenue' => DB::table('orders')->sum('final_amount') ?? 0,
            'total_orders' => DB::table('orders')->count() ?? 0,
            'average_order_value' => DB::table('orders')->avg('final_amount') ?? 0,
            'orders_last_hour' => DB::table('orders')
                ->where('created_at', '>=', $oneHourAgo)
                ->count() ?? 0,
            'revenue_change_last_hour' => $this->calculateRevenueChangeLastHour(),
            'top_products' => $this->getTopProducts(5)
        ];
    }

    /**
     * Calculate revenue change in the last hour
     */
    protected function calculateRevenueChangeLastHour(): float
    {
        $twoHoursAgo = Carbon::now()->subHours(2);
        $oneHourAgo = Carbon::now()->subHour();

        $previousHourRevenue = DB::table('orders')
            ->whereBetween('created_at', [$twoHoursAgo, $oneHourAgo])
            ->sum('final_amount') ?? 0;

        $currentHourRevenue = DB::table('orders')
            ->where('created_at', '>=', $oneHourAgo)
            ->sum('final_amount') ?? 0;

        if ($previousHourRevenue == 0) {
            return $currentHourRevenue > 0 ? 100.0 : 0.0;
        }

        return round((($currentHourRevenue - $previousHourRevenue) / $previousHourRevenue) * 100, 2);
    }

    /**
     * Get top selling products
     */
    /**
     * Get top selling products from orders table directly
     */
    protected function getTopProducts(int $limit = 5): array
    {
        
        if (Schema::hasColumn('orders', 'product_id')) {
            return DB::table('orders')
                ->select(
                    'products.name',
                    DB::raw('SUM(orders.quantity) as total_sold'),
                    DB::raw('SUM(orders.final_amount) as total_revenue')
                )
                ->join('products', 'orders.product_id', '=', 'products.id')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_sold')
                ->limit($limit)
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'total_sold' => (int)$item->total_sold,
                        'total_revenue' => (float)$item->total_revenue
                    ];
                })
                ->toArray();
        }
        return DB::table('orders')
            ->select(
                'product_name as name',
                DB::raw('SUM(quantity) as total_sold'),
                DB::raw('SUM(final_amount) as total_revenue')
            )
            ->groupBy('product_name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'total_sold' => (int)$item->total_sold,
                    'total_revenue' => (float)$item->total_revenue
                ];
            })
            ->toArray();
    }

    /**
     * Get current weather data using WeatherService
     */
    protected function getCurrentWeatherData(): array
    {
        try {
            $weatherData = $this->weatherService->getCurrentWeather();

            return [
                'temperature' => $weatherData['temperature'],
                'condition' => $weatherData['condition'],
                'humidity' => $weatherData['humidity'],
                'city' => $weatherData['city'],
                'country' => $weatherData['country'],
                'feels_like' => $weatherData['feels_like'],
                'description' => $weatherData['description']
            ];
        } catch (\Exception $e) {
            // Fallback to default weather data if service fails
            return [
                'temperature' => 25,
                'condition' => 'Clear',
                'humidity' => 50,
                'city' => 'Cairo',
                'country' => 'EG',
                'feels_like' => 25,
                'description' => 'Default weather data'
            ];
        }
    }
}
