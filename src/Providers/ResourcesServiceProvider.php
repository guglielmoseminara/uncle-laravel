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

        $migrationFromResources = [];
        $resourcesPath = app_path() . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR;
        $resourcesDatabasePath = DIRECTORY_SEPARATOR . 'Database';
        $resourcesMigrationPath = $resourcesDatabasePath . DIRECTORY_SEPARATOR . 'migrations';

        foreach(config('app.resources') as $resource => $classPath) {
            $this->app->singleton($resource.'Resource', function ($app) use ($classPath) {
                return new $classPath($app);
            });

            if (\File::isDirectory($resourcesPath . $resource . $resourcesDatabasePath) && \File::isDirectory($resourcesPath . $resource . $resourcesMigrationPath))
                array_push($migrationFromResources, $resourcesPath . $resource . $resourcesMigrationPath);
        }

        // load migrations in Resources
        $this->loadMigrationsFrom(
            $migrationFromResources
        );

        /*if (! app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load;
        }*/
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
