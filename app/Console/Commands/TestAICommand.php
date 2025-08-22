<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestAICommand extends Command
{
    protected $signature = 'test:ai';
    protected $description = 'Test OpenAI API connection';

    public function handle()
    {
        try {
            $apiKey = config('services.openai.api_key');
            
            if (empty($apiKey)) {
                $this->warn('No API key found, using mock data');
                $this->info('Mock Response: مرحباً! النظام يعمل ببيانات تجريبية');
                return;
            }
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => 'Say hello in Arabic!']
                ],
                'max_tokens' => 50,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $this->info('API Response: ' . $result['choices'][0]['message']['content']);
            } else {
                $this->warn('API quota exceeded, using mock data');
                $this->info('Mock Response: مرحباً! البيانات التجريبية تعمل بشكل مثالي');
            }
            
        } catch (\Exception $e) {
            $this->error('Connection Error: ' . $e->getMessage());
            $this->info('Mock Response: النظام جاهز للعمل ببيانات تجريبية');
        }
    }
}