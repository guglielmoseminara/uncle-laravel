<?php

namespace UncleProject\UncleLaravel\Command\Resource;

use UncleProject\UncleLaravel\Command\Resource\BaseResourceCommand;

class GenerateCommand extends BaseResourceCommand
{



    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource:generate {resource} ';

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
        $this->resourceName = $names['plural'];

        $this->resourcePath = app_path('Http'.DIRECTORY_SEPARATOR.'Resources'). DIRECTORY_SEPARATOR. $this->resourceName;

        if (\File::exists($this->resourcePath)) {
            $this->error($this->resourceName  . ' resource already exists');
            return;
        }

        \File::makeDirectory($this->resourcePath);

        $this->makeResourceFile();
        $this->makeResourceRoute($names['singular']);

        $this->makeResourceControllers($names['singular']);
        $this->makeResourceFakers($names['singular']);
        $this->makeResourceModels($names['singular']);
        $this->makeResourcePresenters($names['singular']);
        $this->makeResourceRepositories($names['singular']);
        $this->makeResourceRequests($names['singular']);

        $this->makeDatabaseFile($names['singular']);
        $this->makeTestFile($names['singular']);

        $this->writeInFile(
            config_path('app.php'),
            '//Add Resource - Uncle Comment (No Delete)',
            $this->compileStub(
                ['{resourceName}'],
                [$this->resourceName],
                __DIR__.'/stubs/AddResourcePath.stub')
        );

        $this->info("Resource {$this->resourceName} generate successfully");
    }
}
