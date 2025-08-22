<?php

namespace App\Foundation\Repositories\Interfaces;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function create(array $data): Order;
    
    public function getAnalytics(): array;
    
    public function getOrdersLastMinute(): Collection;
    
    public function getTotalRevenue(): float;
    
    public function getTopProductsBySales(int $limit = 5): Collection;
    
    public function getRevenueChangeLastMinute(): float;
    
    public function getRecentOrdersForAI(int $limit = 20): Collection;
}
