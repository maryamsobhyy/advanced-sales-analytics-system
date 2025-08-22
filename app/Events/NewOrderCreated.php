<?php
// app/Events/NewOrderCreated.php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewOrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'new-order';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'product_name' => $this->order->product->name,
            'quantity' => $this->order->quantity,
            'final_amount' => $this->order->final_amount,
            'weather_discount' => $this->order->weather_discount,
            'created_at' => $this->order->created_at,
            'weather_info' => $this->order->weather_data,
        ];
    }
}