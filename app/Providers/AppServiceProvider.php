<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\ChannelManager;
use App\Notifications\Channels\SmsChannel;
use App\Models\Treatment;
use App\Observers\TreatmentObserver;

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

        // Register Treatment Observer
        Treatment::observe(TreatmentObserver::class);
    }
}
