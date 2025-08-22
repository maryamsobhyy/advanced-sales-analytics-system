<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'category',
        'temperature_preference',
        'is_active'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeHotDrinks($query)
    {
        return $query->where('temperature_preference', 'hot');
    }

    public function scopeColdDrinks($query)
    {
        return $query->where('temperature_preference', 'cold');
    }

    // Helper methods
    public function getTotalSalesAttribute()
    {
        return $this->orders()->sum('final_amount');
    }

    public function getTotalQuantitySoldAttribute()
    {
        return $this->orders()->sum('quantity');
    }
}
