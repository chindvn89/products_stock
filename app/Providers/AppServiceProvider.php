<?php

namespace App\Providers;

use App\Repositories\Eloquent\ProductEloquentRepository;
use App\Repositories\Eloquent\StockEloquentRepository;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\StockRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductEloquentRepository::class);

        $this->app->bind(StockRepositoryInterface::class, StockEloquentRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
