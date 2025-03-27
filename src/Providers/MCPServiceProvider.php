<?php

namespace InnoGE\LaravelMcp\Providers;

use Illuminate\Support\ServiceProvider;
use InnoGE\LaravelMcp\ModelContextProtocol;

class MCPServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ModelContextProtocol::class, function ($app) {
            return new ModelContextProtocol;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {}
}
