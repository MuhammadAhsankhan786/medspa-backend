<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\ChannelManager;
use App\Notifications\Channels\SmsChannel;
use App\Models\Treatment;
use App\Observers\TreatmentObserver;
use App\Models\StockAdjustment;
use App\Observers\StockAdjustmentObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Custom SMS channel
        $this->app->make(ChannelManager::class)->extend('sms', function ($app) {
            return new SmsChannel();
        });

        // Register Observers
        Treatment::observe(TreatmentObserver::class);
        StockAdjustment::observe(StockAdjustmentObserver::class); // 👈 add this line
    }
}
