<?php

namespace UncleProject\UncleLaravel\Command\Resource;


class ModelCommand extends BaseResourceCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uncle:resource:create-model {resource} {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource in project';

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
        $this->resourceName = $this->argument('resource');

        $this->resourcePath = app_path('Http'.DIRECTORY_SEPARATOR.'Resources'). DIRECTORY_SEPARATOR. $this->resourceName;

        if (!\File::exists($this->resourcePath)) {
            $this->error($this->resourceName  . ' resource not exists');
            return;
        }

        $names = $this->resolveResourceName($this->argument('model'));
        $this->makeResourceModels($names['singular']);

        $this->info("Model {$names['singular']} in Resource {$this->resourceName} generate successfully");

    }

}
