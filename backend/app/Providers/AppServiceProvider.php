<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repositories
        $this->app->bind(
            \App\Repositories\Interfaces\ProductRepositoryInterface::class,
            \App\Repositories\Eloquent\ProductRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\OrderRepositoryInterface::class,
            \App\Repositories\Eloquent\OrderRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\TenantRepositoryInterface::class,
            \App\Repositories\Eloquent\TenantRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\CategoryRepositoryInterface::class,
            \App\Repositories\Eloquent\CategoryRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\CustomerRepositoryInterface::class,
            \App\Repositories\Eloquent\CustomerRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
