<?php

namespace App\Foundation\Services;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherService
{
  
    private string $apiKey;
    private string $baseUrl;
    private string $defaultCity;

    public function __construct()
    {
        $this->apiKey = config('services.openweather.api_key');
        $this->baseUrl = 'https://api.openweathermap.org/data/2.5';
        $this->defaultCity = config('services.openweather.default_city', 'Cairo');
    }

    public function getCurrentWeather(string $city = null): array
    {
        $city = $city ?? $this->defaultCity;
        $cacheKey = "weather_current_{$city}";
        
        // Cache weather data for 10 minutes to avoid excessive API calls
        return Cache::remember($cacheKey, 600, function () use ($city) {
            return $this->fetchCurrentWeather($city);
        });
    }

    private function fetchCurrentWeather(string $city): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/weather", [
                'q' => $city,
                'appid' => $this->apiKey,
                'units' => 'metric' // Celsius
            ]);

            if (!$response->successful()) {
                Log::error('Weather API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return $this->getDefaultWeatherData();
            }

            $data = $response->json();
            
            return [
                'temperature' => round($data['main']['temp']),
                'feels_like' => round($data['main']['feels_like']),
                'humidity' => $data['main']['humidity'],
                'condition' => $data['weather'][0]['main'],
                'description' => $data['weather'][0]['description'],
                'city' => $data['name'],
                'country' => $data['sys']['country'],
                'timestamp' => now(),
                'raw_data' => $data // Store complete response for debugging
            ];
            
        } catch (\Exception $e) {
            Log::error('Weather Service Exception', [
                'message' => $e->getMessage(),
                'city' => $city
            ]);
            
            return $this->getDefaultWeatherData();
        }
    }

    private function getDefaultWeatherData(): array
    {
        return [
            'temperature' => 25,
            'feels_like' => 25,
            'humidity' => 50,
            'condition' => 'Clear',
            'description' => 'Default weather (API unavailable)',
            'city' => $this->defaultCity,
            'country' => 'EG',
            'timestamp' => now(),
            'raw_data' => null
        ];
    }

    public function getWeatherConditionType(int $temperature): string
    {
        if ($temperature <= 5) return 'very_cold';
        if ($temperature <= 15) return 'cold';
        if ($temperature <= 25) return 'moderate';
        if ($temperature <= 35) return 'hot';
        return 'very_hot';
    }

    public function shouldPromoteHotDrinks(array $weatherData): bool
    {
        return $weatherData['temperature'] < 15;
    }

    public function shouldPromoteColdDrinks(array $weatherData): bool
    {
        return $weatherData['temperature'] > 25;
    }
}
