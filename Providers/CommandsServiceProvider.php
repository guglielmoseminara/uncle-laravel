<?php

namespace UncleProject\UncleLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use UncleProject\UncleLaravel\Command\ResourceCommand;
use App;

class CommandsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            ResourceCommand::class,
        ]);
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
