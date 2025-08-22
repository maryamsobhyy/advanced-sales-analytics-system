<?php

namespace App\Foundation\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AiService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->baseUrl = 'https://api.openai.com/v1';
    }

    public function generateRecommendations(array $salesData): string
    {
        if (empty($this->apiKey)) {
            return $this->getMockRecommendations($salesData);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)
                ->post($this->baseUrl . '/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a helpful sales analyst assistant. Provide concise, actionable recommendations for product promotions based on sales data.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->buildPrompt($salesData)
                        ]
                    ],
                    'max_tokens' => 500,
                    'temperature' => 0.7
                ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'] ??
                    $this->getMockRecommendations($salesData);
            }
            return $this->getMockRecommendations($salesData);
        } catch (\Exception $e) {
            Log::warning('AI API failed, using mock data: ' . $e->getMessage());
            return $this->getMockRecommendations($salesData);
        }
    }

    /**
     * Generate realistic mock recommendations based on sales data
     */
    /**
     * Generate realistic mock recommendations based on sales data
     */
    protected function getMockRecommendations(array $salesData): string
    {
        $temp = $salesData['current_weather']['temperature'] ?? 25;
        $topProduct = $salesData['top_products'][0]['name'] ?? 'Iced Coffee';
        $humidity = $salesData['current_weather']['humidity'] ?? 60;

        if ($temp > 30) {
            return " **توصيات بناء على الطقس الحار (" . $temp . "°C):**
         ركزي على المشروبات الباردة والمنعشة:
• {$topProduct} - عرض 'اشتري 2 بخصم 20%'
• عصائر طازجة - أضيفيها للقائمة اليومية
• مثلجات - روجي لها كتحلية منعشة

استراتيجية تسعير:
• خفض أسعار المشروبات الباردة 10%
• زيادة أسعار المشروبات الساخنة 5%

 متوقع زيادة مبيعات: 30-40% خلال الأسبوع

 التوقيت المثالي: 2-6 مساءً عندما الحر يكون شديد

 الرطوبة: {$humidity}% - المشروبات المنعشة مطلوبة";
        } elseif ($temp < 15) {
            return " **توصيات بناء على الطقس البارد (" . $temp . "°C):**
        
 ركزي على المشروبات الساخنة والدافئة:
• قهوة ساخنة - عرض 'كوب مجاني على الشراء الثالث'
• شوكولاتة ساخنة - أضيفي marshmallows مجاناً
• شاي أعشاب - روجي للأنواع المهدئة

 استراتيجية تسعير:
• زيادة أسعار المشروبات الساخنة 8%
• خفض أسعار المشروبات الباردة 12%

 متوقع زيادة مبيعات: 25-35% خلال الأسبوع

 التوقيت المثالي: 8-11 صباحاً و7-10 مساءً

 الرطوبة: {$humidity}% - المشروبات الدافئة مطلوبة";
        } else {
            return " **توصيات بناء على الطقس المعتدل (" . $temp . "°C):**
        
 ركزي على التنوع في العروض:
• {$topProduct} - استمري في الترويج له
• عروض combo - مشروب + snack بخصم 15%
• تقديم عينات مجانية من المنتجات الجديدة

 استراتيجية تسعير:
• أسعار ثابتة مع عروض موسمية
• خصومات على الكميات الكبيرة

 متوقع زيادة مبيعات: 15-25% خلال الأسبوع

 التوقيت المثالي: فترات الذروة 12-2 ظهراً و5-8 مساءً

 الرطوبة: {$humidity}% - طقس مثالي للتنوع";
        }
    }

    /**
     * Build the prompt for AI analysis
     */
    protected function buildPrompt(array $salesData): string
    {
        return "
        Given the following sales data analysis, provide specific product promotion recommendations:

        Sales Overview:
        - Total Revenue: {$salesData['total_revenue']}
        - Total Orders: {$salesData['total_orders']}
        - Average Order Value: {$salesData['average_order_value']}
        
        Recent Activity (Last 60 minutes):
        - New Orders: {$salesData['orders_last_hour']}
        - Revenue Change: {$salesData['revenue_change_last_hour']}%
        
        Top Performing Products:
        " . $this->formatTopProducts($salesData['top_products']) . "

        Weather Conditions:
        - Current Temperature: {$salesData['current_weather']['temperature']}°C
        - Conditions: {$salesData['current_weather']['condition']}
        - Humidity: {$salesData['current_weather']['humidity']}%

        Based on this data, please provide:
        1. Which products should we promote immediately?
        2. Specific promotion strategies considering the weather
        3. Pricing recommendations
        4. Expected impact on revenue

        Please be specific and actionable in your recommendations.
        ";
    }

    /**
     * Format top products for the prompt
     */
    protected function formatTopProducts(array $topProducts): string
    {
        $formatted = "";
        foreach ($topProducts as $index => $product) {
            $formatted .= ($index + 1) . ". {$product['name']} - {$product['total_sold']} units sold - ${$product['total_revenue']} revenue\n";
        }
        return $formatted;
    }

    /**
     * Alternative method using Gemini API
     */
    public function generateRecommendationsWithGemini(array $salesData): string
    {
        return "Gemini integration coming soon...";
    }
}
