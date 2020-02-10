<?php

namespace UncleProject\UncleLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use UncleProject\UncleLaravel\Command\Resource\GenerateCommand;
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
            \UncleProject\UncleLaravel\Command\Resource\GenerateCommand::class,
            \UncleProject\UncleLaravel\Command\Resource\ModelCommand::class,
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
