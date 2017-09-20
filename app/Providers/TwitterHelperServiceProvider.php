<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\TwitterHelper;

class TwitterHelperServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Contracts\TwitterHelperContract', function($app) {
            return new TwitterHelper(
                $app->make('Thujohn\Twitter\Twitter'),
                $app->make('Illuminate\Contracts\Cache\Repository')
            );
        });
    }

    public function provides()
    {
        return ['App\Contracts\TwitterHelperContract'];
    }

}
