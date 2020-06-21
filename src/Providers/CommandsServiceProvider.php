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
            \UncleProject\UncleLaravel\Command\Relation\HasOneRelationCommand::class,
            \UncleProject\UncleLaravel\Command\Relation\HasManyRelationCommand::class,
            \UncleProject\UncleLaravel\Command\Relation\BelongsToRelationCommand::class,
            \UncleProject\UncleLaravel\Command\Relation\BelongsToManyRelationCommand::class,
            \UncleProject\UncleLaravel\Command\Relation\MorphOneRelationCommand::class,
            \UncleProject\UncleLaravel\Command\Relation\MorphManyRelationCommand::class,

            //Project
            \UncleProject\UncleLaravel\Command\ProjectCreateCommand::class,
            \UncleProject\UncleLaravel\Command\XMLCompileCommand::class,
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
