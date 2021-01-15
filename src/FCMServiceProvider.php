<?php

namespace LaravelFCM;

use Illuminate\Support\Str;
use LaravelFCM\Sender\FCMGroup;
use LaravelFCM\Sender\FCMSender;
use Illuminate\Support\ServiceProvider;

class FCMServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        if (Str::contains($this->app->version(), 'Lumen')) {
            $this->app->configure('fcm');
        } else {
            $this->publishes(
                [
                    __DIR__ . '/../config/fcm.php' => config_path('fcm.php'),
                ]
            );
        }
    }

    public function register()
    {
        if (!Str::contains($this->app->version(), 'Lumen')) {
            $this->mergeConfigFrom(__DIR__.'/../config/fcm.php', 'fcm');
        }

        $this->app->singleton(FCMManager::class, function ($app) {
            return (new FCMManager($app))->driver();
        });

        $this->app->bind(FCMGroup::class, function ($app) {
            $client = $app[FCMManager::class];
            $url = $app[ 'config' ]->get('fcm.http.server_group_url');

            return new FCMGroup($client, $url);
        });

        $this->app->bind(FCMSender::class, function ($app) {
            $client = $app[FCMManager::class];
            $url = $app[ 'config' ]->get('fcm.http.server_send_url');

            return new FCMSender($client, $url);
        });
    }

    public function provides()
    {
        return [FCMManager::class, FCMGroup::class, FCMSender::class];
    }
}
