# advanced-sales-analytics-systemA Laravel-based real-time sales dashboard with AI-powered recommendations and weather-based dynamic pricing. Features live order tracking, instant analytics updates, and intelligent product promotion suggestions.

GitHub Repository

https://github.com/maryamsobhyy/advanced-sales-analytics-system

manual setup 
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve

Features
Real-time Order Management - Live order creation and tracking

Instant Analytics - Real-time sales metrics and revenue monitoring

WebSocket Integration - Live updates using Pusher

AI-Powered Recommendations - ChatGPT integration for strategic insights

Weather-Based Pricing - Dynamic pricing using OpenWeatherMap API

Dockerized Environment - Easy setup and deployment

Architecture & Design Patterns
Patterns Used:
Repository Pattern - Data access abstraction

Service Pattern - Business logic encapsulation

Observer Pattern - Event-driven architecture

Factory Pattern - Object creation

Strategy Pattern - Weather-based pricing strategies

Project Structure:
text
app/
├── Console/
├── Events/          # Broadcast events
├── Listeners/       # Event listeners
├── Models/          # Eloquent models
├── Repositories/    # Data access layer
├── Services/        # Business logic
│   ├── WeatherService.php
│   └── AIService.php
└── Http/
    ├── Controllers/ # API controllers
    └── Requests/    # Form requests
Environment Variables
text
# Database
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=sales_analytics
DB_USERNAME=root
DB_PASSWORD=password

# Pusher (WebSockets)
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=eu

# OpenAI
OPENAI_API_KEY=your_openai_key

# Weather API
OPENWEATHERMAP_API_KEY=your_weather_key
WEATHER_BASE_URL=https://api.openweathermap.org/data/2.5
API Endpoints
Orders Management
POST /api/orders - Create new order

GET /api/orders - List all orders

GET /api/orders/{id} - Get specific order

Analytics
GET /api/analytics - Real-time sales analytics

GET /api/analytics/realtime - Live metrics (last minute)

AI Recommendations
GET /api/recommendations - AI-generated product suggestions

Real-Time Implementation
WebSocket Events:
php
// Event triggered on new order
class NewOrderCreated implements ShouldBroadcast {
    public function broadcastOn() {
        return new Channel('orders');
    }
}

// Frontend subscription
const channel = pusher.subscribe('orders');
channel.bind('new-order', (data) => {
    // Update UI in real-time
});
Live Metrics Tracked:
Orders per minute

Revenue changes

Top products

Weather-impacted sales

AI Integration
Implementation Details:
php
class AIService {
    public function generateRecommendations($salesData) {
        $prompt = "Given this sales data: {$salesData}, which products should we promote for higher revenue?";
        return $this->openAI->complete($prompt);
    }
}
Sample AI Prompt:
"Analyze recent sales trends and suggest optimal product promotions based on weather conditions and historical performance."

Weather Integration
Dynamic Pricing Logic:
php
class WeatherService {
    public function applyWeatherPricing(Order $order) {
        $weather = $this->getCurrentWeather();
        
        if ($weather['temp'] > 30) {
            // Apply cold drink discount
            $order->weather_discount = 0.1;
        } elseif ($weather['temp'] < 10) {
            // Apply hot drink premium
            $order->weather_discount = -0.05;
        }
    }
}


# Configure environment variables
# Update DB, Pusher, OpenAI, and Weather API keys in .env

# Run migrations
php artisan migrate

# Start server
php artisan serve
Testing
Run Tests:
bash
# Run all tests
docker-compose exec app php artisan test

# Run specific test
docker-compose exec app php artisan test --filter OrderTest
Sample Test Case:
php
public function test_order_creation_triggers_event()
{
    Event::fake();
    
    $response = $this->postJson('/api/orders', [
        'product_id' => 1,
        'quantity' => 2,
        'price' => 25.00
    ]);
    
    Event::assertDispatched(NewOrderCreated::class);
    $response->assertStatus(201);
}
Docker Setup
Services:
app - Laravel application (PHP 8.2 + Nginx)

db - MySQL database

redis - Cache and session storage

queue - Queue worker for async jobs

docker-compose.yml:
yaml
version: '3.8'
services:
  app:
    build: .
    ports:
      - "8000:80"
    depends_on:
      - db
      - redis

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: password
      MYSQL_DATABASE: sales_analytics

  redis:
    image: redis:alpine
Database Schema
Key Tables:
orders - Sales transactions

products - Product catalog

weather_logs - Historical weather data

analytics_snapshots - Periodic metrics

Relationships:
Orders belongsTo Products

Analytics hasMany Orders

Weather affects Order pricing

Future Enhancements
Mobile app integration

Advanced predictive analytics

Multi-store support

Email/SMS notifications

Advanced dashboard with charts

Inventory management integration

Development Approach
AI-Assisted (30%):
Code scaffolding and boilerplate

Documentation generation

Prompt engineering for AI recommendations

API client implementations

Manual Implementation (70%):
Business logic and algorithms

Database design and optimization

Real-time WebSocket integration

Weather-based pricing strategies

Testing and deployment pipeline

