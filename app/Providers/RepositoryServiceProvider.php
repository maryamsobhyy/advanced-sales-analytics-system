<?php
// app/Providers/RepositoryServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Foundation\Repositories\OrderRepository;
use App\Foundation\Repositories\Interfaces\OrderRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
    }

    public function boot(): void
    {
        //
    }
}