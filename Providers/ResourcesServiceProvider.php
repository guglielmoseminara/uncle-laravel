<?php

namespace UncleProject\UncleLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use App;

class ResourcesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Resources', function ($app) {
            return new App\Helpers\Resources($app);
        });
        foreach(config('app.resources') as $resource => $classPath) {
            $this->app->singleton($resource.'Resource', function ($app) use ($classPath) {
                return new $classPath($app);
            });    
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
