<?php

namespace UncleProject\UncleLaravel\Command;

use Illuminate\Console\Command;

class ResourceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource:create {resource} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send drip e-mails to a user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $resourceName = ucfirst($this->argument('resource'));

        $resourceNameSingular = str_singular($resourceName);

        if($resourceNameSingular == $resourceName){
            $resourceName = str_plural($resourceNameSingular);
        }

        $path = app_path('Http'.DIRECTORY_SEPARATOR.'Resources');

        $resourcePath = $path . DIRECTORY_SEPARATOR. $resourceName;

        if (\File::exists($resourcePath)) {
            $this->error($resourceName  . ' component already exists');
            return;
        }

        \File::makeDirectory($resourcePath);

        \File::makeDirectory($resourcePath.DIRECTORY_SEPARATOR.'Controllers');
        \File::makeDirectory($resourcePath.DIRECTORY_SEPARATOR.'Fakers');
        \File::makeDirectory($resourcePath.DIRECTORY_SEPARATOR.'Models');
        \File::makeDirectory($resourcePath.DIRECTORY_SEPARATOR.'Presenters');
        \File::makeDirectory($resourcePath.DIRECTORY_SEPARATOR.'Repositories');
        \File::makeDirectory($resourcePath.DIRECTORY_SEPARATOR.'Requests');

    }
}