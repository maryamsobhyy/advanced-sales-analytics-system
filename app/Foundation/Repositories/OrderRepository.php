<?php

namespace App\Foundation\Repositories;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use App\Foundation\Repositories\Interfaces\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(array $data): Order
    {
        return Order::create($data);
    }
    
    public function getAnalytics(): array
    {
        return [
            'total_revenue' => $this->getTotalRevenue(),
            'top_products' => $this->getTopProductsBySales(),
            'revenue_change_last_minute' => $this->getRevenueChangeLastMinute(),
            'orders_count_last_minute' => $this->getOrdersLastMinute()->count(),
            'total_orders_today' => Order::today()->count(),
            'average_order_value' => $this->getAverageOrderValue(),
        ];
    }
    
    public function getOrdersLastMinute(): Collection
    {
        return Order::lastMinute()
                   ->with('product')
                   ->orderBy('created_at', 'desc')
                   ->get();
    }
    
    public function getTotalRevenue(): float
    {
        
        $result = DB::select("
            SELECT COALESCE(SUM(final_amount), 0) as total_revenue 
            FROM orders
        ");
        
        return (float) $result[0]->total_revenue;
    }
    

public function getTopProductsBySales(int $limit = 5): Collection
{
    $topProducts = DB::select("
        SELECT 
            p.id,
            p.name,
            p.category,
            p.temperature_preference,
            COUNT(o.id) as total_orders,
            SUM(o.quantity) as total_quantity_sold,
            SUM(o.final_amount) as total_revenue,
            AVG(o.final_amount) as average_order_value,
            AVG(o.weather_discount) as average_discount
        FROM products p
        INNER JOIN orders o ON p.id = o.product_id
        GROUP BY p.id, p.name, p.category, p.temperature_preference
        ORDER BY total_revenue DESC
        LIMIT ?
    ", [$limit]);


    return Product::hydrate($topProducts);
}
    
    public function getRevenueChangeLastMinute(): float
    {
        $lastMinuteRevenue = DB::select("
            SELECT COALESCE(SUM(final_amount), 0) as revenue
            FROM orders 
            WHERE created_at >= ?
        ", [Carbon::now()->subMinute()]);
        
        $previousMinuteRevenue = DB::select("
            SELECT COALESCE(SUM(final_amount), 0) as revenue
            FROM orders 
            WHERE created_at >= ? AND created_at < ?
        ", [
            Carbon::now()->subMinutes(2),
            Carbon::now()->subMinute()
        ]);
        
        $current = (float) $lastMinuteRevenue[0]->revenue;
        $previous = (float) $previousMinuteRevenue[0]->revenue;
        
        if ($previous == 0) {
            return $current > 0 ? 100 : 0; // 100% increase from 0
        }
        
        return round((($current - $previous) / $previous) * 100, 2);
    }
    
    public function getRecentOrdersForAI(int $limit = 20): Collection
    {
        return Order::with(['product:id,name,category,temperature_preference,base_price'])
                   ->select([
                       'id', 'product_id', 'quantity', 'unit_price', 'final_amount',
                       'weather_discount', 'weather_data', 'created_at'
                   ])
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }
    
    private function getAverageOrderValue(): float
    {
        $result = DB::select("
            SELECT COALESCE(AVG(final_amount), 0) as avg_order_value 
            FROM orders
        ");
        
        return round((float) $result[0]->avg_order_value, 2);
    }
}

