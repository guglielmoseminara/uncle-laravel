<?php

namespace UncleProject\UncleLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use UncleProject\UncleLaravel\Helpers\Utils;
use App;

class UtilsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Utils', function ($app) {
            return new Utils($app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }

}
