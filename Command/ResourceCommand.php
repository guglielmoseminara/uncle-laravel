<?php

namespace UncleProject\UncleLaravel\Command;

use UncleProject\UncleLaravel\Classes\BaseCommand;

class ResourceCommand extends BaseCommand
{

    private $resourceName;
    private $resourceSingleName;
    private $resourcePath;


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

        if (\File::exists($this->resourcePath)) {
            $this->error($this->resourceName  . ' resource already exists');
            return;
        }

        \File::makeDirectory($this->resourcePath);

        $this->makeResourceControllers();
        $this->makeResourceFakers();
        $this->makeResourceModels();
        $this->makeResourcePresenters();
        $this->makeResourceRepositories();
        $this->makeResourceRequests();
        $this->makeResourceFile();
        $this->makeResourceRoute();

        $this->makeDatabaseFile();

        $this->makeTestFile();

        $this->writeInFile(
            config_path('app.php'),
            '//Add Resource - Uncle Comment (No Delete)',
            $this->compileStub(
                ['{resourceName}'],
                [$this->resourceName],
                __DIR__.'/stubs/Resource/AddResourcePath.stub')

        );

    }

    private function makeResourceControllers(){

        $controllersPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Controllers';
        $controllersVersionPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.'V1';

        \File::makeDirectory($controllersPath);
        \File::makeDirectory($controllersVersionPath);


        \File::put(
            $controllersVersionPath.DIRECTORY_SEPARATOR.$this->resourceSingleName.'Controller.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}'],
                [$this->resourceName,$this->resourceSingleName],
                __DIR__.'/stubs/Resource/Controller.stub')
        );
    }

    private function makeResourceFakers(){

        $fakersPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Fakers';
        \File::makeDirectory($fakersPath);

        \File::put(
            $fakersPath.DIRECTORY_SEPARATOR.$this->resourceSingleName.'Faker.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}'],
                [$this->resourceName,$this->resourceSingleName],
                __DIR__.'/stubs/Resource/Faker.stub')
        );
    }

    private function makeResourceModels(){

        $modelsPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Models';
        \File::makeDirectory($modelsPath);

        \File::put(
            $modelsPath.DIRECTORY_SEPARATOR.$this->resourceSingleName.'.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}'],
                [$this->resourceName,$this->resourceSingleName],
                __DIR__.'/stubs/Resource/Model.stub')
        );

    }

    private function makeResourcePresenters(){

        $presentersPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Presenters';
        \File::makeDirectory($presentersPath);

        \File::put($presentersPath.DIRECTORY_SEPARATOR.$this->resourceSingleName.'Presenter.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}', '{resourceSingleNameLower}'],
                [$this->resourceName,$this->resourceSingleName, strtolower($this->resourceSingleName)],
                __DIR__.'/stubs/Resource/Presenter.stub')
        );
    }

    private function makeResourceRepositories(){

        $repositoriesPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Repositories';
        \File::makeDirectory($repositoriesPath);

        \File::put($repositoriesPath.DIRECTORY_SEPARATOR.$this->resourceSingleName.'Repository.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}', '{resourceNameLower}', '{resourceSingleNameLower}'],
                [$this->resourceName,$this->resourceSingleName, strtolower($this->resourceName), strtolower($this->resourceSingleName)],
                __DIR__.'/stubs/Resource/Repository.stub')
        );

    }

    private function makeResourceRequests(){

        $requestPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Requests';
        \File::makeDirectory($requestPath);

        \File::put($requestPath.DIRECTORY_SEPARATOR.$this->resourceSingleName.'Request.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}'],
                [$this->resourceName,$this->resourceSingleName],
                __DIR__.'/stubs/Resource/Request.stub')
        );

    }

    private function makeResourceFile(){

        \File::put($this->resourcePath.DIRECTORY_SEPARATOR.$this->resourceName.'Resource.php',
            $this->compileStub(
                '{resourceName}',
                $this->resourceName,
                __DIR__.'/stubs/Resource/Resource.stub')
        );

    }

    private function makeResourceRoute(){

        \File::put($this->resourcePath.DIRECTORY_SEPARATOR.$this->resourceName.'Routes.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}', '{resourceNameLower}'],
                [$this->resourceName,$this->resourceSingleName, strtolower($this->resourceName)],
                __DIR__.'/stubs/Resource/Route.stub')
        );

    }

    private function makeDatabaseFile(){
        $this->call('make:migration', [ 'name' => 'create_'.strtolower($this->resourceName).'_table']);
        $this->call('make:factory', [ 'name' => $this->resourceSingleName.'Factory', '--model' => $this->resourceSingleName]);
        $this->call('make:seeder', [ 'name' => $this->resourceName.'TableSeeder']);
    }

    private function makeTestFile(){

        $testPath = base_path('tests'.DIRECTORY_SEPARATOR.'Api'.DIRECTORY_SEPARATOR.'V1'.DIRECTORY_SEPARATOR.$this->resourceSingleName);
        \File::makeDirectory($testPath);

        \File::put($testPath.DIRECTORY_SEPARATOR.$this->resourceSingleName.'Test.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}', '{resourceNameLower}', '{resourceSingleNameLower}'],
                [$this->resourceName,$this->resourceSingleName, strtolower($this->resourceName), strtolower($this->resourceSingleName)],
                __DIR__.'/stubs/Resource/Test.stub')
        );

    }
}
