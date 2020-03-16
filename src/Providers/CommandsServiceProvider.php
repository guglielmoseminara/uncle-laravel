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
            //Resource
            \UncleProject\UncleLaravel\Command\Resource\GenerateCommand::class,
            \UncleProject\UncleLaravel\Command\Resource\ModelCommand::class,
            \UncleProject\UncleLaravel\Command\Resource\PresenterCommand::class,
            \UncleProject\UncleLaravel\Command\Resource\NotificationCommand::class,

            //Relation
            \UncleProject\UncleLaravel\Command\Relation\OneToOneRelationCommand::class,
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
