<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'unit_price',
        'total_amount',
        'weather_discount',
        'final_amount',
        'weather_data',
        'order_date'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'weather_discount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'weather_data' => 'array',
        'order_date' => 'datetime',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes for analytics
    public function scopeLastMinute($query)
    {
        return $query->where('created_at', '>=', Carbon::now()->subMinute());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
    }

    // Helper methods
    public function calculateDiscount($weatherTemp, $weatherCondition)
    {
        $discount = 0;
        $productTempPreference = $this->product->temperature_preference;

        // Hot drinks promotion on cold days
        if ($productTempPreference === 'hot' && $weatherTemp < 15) {
            $discount = 10; // 10% discount
        }
        
        // Cold drinks promotion on hot days
        if ($productTempPreference === 'cold' && $weatherTemp > 25) {
            $discount = 15; // 15% discount
        }

        // Extra discount for extreme weather
        if ($weatherTemp < 5 && $productTempPreference === 'hot') {
            $discount = 20;
        } elseif ($weatherTemp > 35 && $productTempPreference === 'cold') {
            $discount = 25;
        }

        return $discount;
    }
}
