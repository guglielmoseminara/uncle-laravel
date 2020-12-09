<?php

namespace UncleProject\UncleLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use UncleProject\UncleLaravel\Helpers\Resources;
use UncleProject\UncleLaravel\Classes\BaseResource;
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
            return new Resources($app);
        });

        $this->app->singleton('UncleResource', function ($app, $parameters) {
            //dd($app, $parameters);
            return new BaseResource($parameters['name']);
        });

        $migrationFromResources = [];
        $resourcesPath = app_path() . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR;
        $resourcesDatabasePath = DIRECTORY_SEPARATOR . 'Database';
        $resourcesMigrationPath = $resourcesDatabasePath . DIRECTORY_SEPARATOR . 'migrations';
        $resourcesFactoriesPath = $resourcesDatabasePath . DIRECTORY_SEPARATOR . 'factories';
        $notificationsPath = DIRECTORY_SEPARATOR . 'Notifications';
        $notificationsViewPath = $notificationsPath . DIRECTORY_SEPARATOR . 'mails';
        $printsPath = DIRECTORY_SEPARATOR . 'Prints';

        foreach(config('uncle.resources') as $resource => $classPath) {
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

            // add Notifications view
            if (\File::isDirectory($resourcesPath . $resource . $notificationsPath)) {
                View::addLocation($resourcesPath . $resource . $notificationsViewPath);
            }
            
            // add Prints view
            if (\File::isDirectory($resourcesPath . $resource . $printsPath)) {
                View::addLocation($resourcesPath . $resource . $printsPath);
            }

            if(\File::exists($resourcesPath . $resource. DIRECTORY_SEPARATOR . $resource ."Routes.php"))
                $this->loadRoutesFrom($resourcesPath.$resource. DIRECTORY_SEPARATOR . $resource ."Routes.php");
        }

        $this->loadMigrationsFrom($migrationFromResources);

        $this->loadRoutesFrom(__DIR__. DIRECTORY_SEPARATOR . '..'.DIRECTORY_SEPARATOR .'XMLCore' . DIRECTORY_SEPARATOR ."XMLRoutes.php");
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
