
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AnalyticsController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Sales Management Routes
Route::group(['prefix' => 'v1'], function () {
    
    // Orders Management
    Route::post('/orders', [OrderController::class, 'store']);
    
    // // Analytics Routes
    Route::get('/analytics', [AnalyticsController::class, 'index']);
    Route::get('/analytics/realtime', [AnalyticsController::class, 'realtimeStats']);
    Route::get('/recommendations', [AnalyticsController::class, 'getRecommendations']);
    
    // Products (for reference)
    Route::get('/products', function () {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Product::active()->get()
        ]);
    });
    Route::get('/weather-test', function() {
    $weatherService = app()->make(\App\Foundation\Services\WeatherService::class);
    $weather = $weatherService->getCurrentWeather('Cairo');

    return response()->json([
        'success' => true,
        'weather' => $weather
    ]);
});
});