<?php

namespace App\Providers;

use App\Services\FeeCalculatorService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FeeCalculatorService::class,function($app){
            return new FeeCalculatorService();
        });
    }

    public function boot(): void
    {
        //
    }
}
