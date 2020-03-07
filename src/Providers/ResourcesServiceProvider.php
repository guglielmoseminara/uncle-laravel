<?php

namespace UncleProject\UncleLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use View;
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
        $resourcesFactoriesPath = $resourcesDatabasePath . DIRECTORY_SEPARATOR . 'factories';
        $notificationsPath = DIRECTORY_SEPARATOR . 'Notifications';
        $notificationsViewPath = $notificationsPath . DIRECTORY_SEPARATOR . 'views';

        foreach(config('app.resources') as $resource => $classPath) {
            $this->app->singleton($resource.'Resource', function ($app) use ($classPath) {
                return new $classPath($app);
            });

            if (\File::isDirectory($resourcesPath . $resource . $resourcesDatabasePath))
            {
                if(\File::isDirectory($resourcesPath . $resource . $resourcesMigrationPath))
                    array_push($migrationFromResources, $resourcesPath . $resource . $resourcesMigrationPath);

                if (! app()->environment('production') && $this->app->runningInConsole() && \File::isDirectory($resourcesPath . $resource . $resourcesFactoriesPath)) {
                    app(Factory::class)->load($resourcesPath . $resource . $resourcesFactoriesPath);
                }
            }

            if (\File::isDirectory($resourcesPath . $resource . $notificationsPath)) {
                View::addLocation($resourcesPath . $resource . $notificationsViewPath);
            }
        }

        $this->loadMigrationsFrom($migrationFromResources);

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
