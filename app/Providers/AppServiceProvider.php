<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\Contracts\RefundGatewayInterface;
use App\Services\Gateways\StripePaymentGateway;
use App\Services\Gateways\StripeRefundGateway;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class,StripePaymentGateway::class);
        $this->app->bind(RefundGatewayInterface::class,StripeRefundGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    
    }
}
