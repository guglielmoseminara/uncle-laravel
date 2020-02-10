<?php

namespace UncleProject\UncleLaravel\Command\Resource;

use UncleProject\UncleLaravel\Classes\BaseCommand;

class ModelCommand extends BaseCommand
{

    private $resourceName;
    private $resourceSingleName;
    private $resourcePath;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource:create-model {resource} {model}';

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
        $names = $this->resolveResourceName($this->argument('resource'));
        $this->resourceSingleName = $names['singular'];
        $this->resourceName = $names['plural'];

        $this->resourcePath = app_path('Http'.DIRECTORY_SEPARATOR.'Resources'). DIRECTORY_SEPARATOR. $this->resourceName;

        if (!\File::exists($this->resourcePath)) {
            $this->error($this->resourceName  . ' resource not exists');
            return;
        }


        $this->makeResourceModels();

    }


    private function makeResourceModels(){

        $modelsPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Models';
        \File::makeDirectory($modelsPath);

        \File::put(
            $modelsPath.DIRECTORY_SEPARATOR.$this->resourceSingleName.'.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}'],
                [$this->resourceName,$this->resourceSingleName],
                __DIR__.'/stubs/Model.stub')
        );

    }


}
