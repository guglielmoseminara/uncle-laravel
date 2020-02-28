<?php

namespace UncleProject\UncleLaravel\Command\Resource;

use UncleProject\UncleLaravel\Classes\BaseCommand;

class BaseResourceCommand extends BaseCommand
{

    protected $resourceName;
    protected $resourcePath;

    
    protected function makeResourceControllers($resourceSingleName){

        $controllersPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Controllers';
        $controllersVersionPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.'V1';

        \File::isDirectory($controllersPath) or\File::makeDirectory($controllersPath);
        \File::isDirectory($controllersVersionPath) or \File::makeDirectory($controllersVersionPath);


        \File::put(
            $controllersVersionPath.DIRECTORY_SEPARATOR.$resourceSingleName.'Controller.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}'],
                [$this->resourceName,$resourceSingleName],
                __DIR__.'/stubs/Controller.stub')
        );
    }

    protected function makeResourceFakers($resourceSingleName){

        $fakersPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Fakers';
        \File::isDirectory($fakersPath) or \File::makeDirectory($fakersPath);

        \File::put(
            $fakersPath.DIRECTORY_SEPARATOR.$resourceSingleName.'Faker.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}'],
                [$this->resourceName,$resourceSingleName],
                __DIR__.'/stubs/Faker.stub')
        );
    }

    protected function makeResourceModels($resourceSingleName){

        $modelsPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Models';
        \File::isDirectory($modelsPath) or \File::makeDirectory($modelsPath);

        \File::put(
            $modelsPath.DIRECTORY_SEPARATOR.$resourceSingleName.'.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}'],
                [$this->resourceName,$resourceSingleName],
                __DIR__.'/stubs/Model.stub')
        );

    }

    protected function makeResourcePresenters($resourceSingleName){

        $presentersPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Presenters';
        \File::isDirectory($presentersPath) or \File::makeDirectory($presentersPath);

        \File::put($presentersPath.DIRECTORY_SEPARATOR.$resourceSingleName.'Presenter.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}', '{resourceSingleNameLower}'],
                [$this->resourceName,$resourceSingleName, strtolower($resourceSingleName)],
                __DIR__.'/stubs/Presenter.stub')
        );
    }

    protected function makeResourceRepositories($resourceSingleName){

        $repositoriesPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Repositories';
        \File::isDirectory($repositoriesPath) or \File::makeDirectory($repositoriesPath);

        \File::put($repositoriesPath.DIRECTORY_SEPARATOR.$resourceSingleName.'Repository.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}', '{resourceNameLower}', '{resourceSingleNameLower}'],
                [$this->resourceName,$resourceSingleName, strtolower($this->resourceName), strtolower($resourceSingleName)],
                __DIR__.'/stubs/Repository.stub')
        );

    }

    protected function makeResourceRequests($resourceSingleName){

        $requestPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Requests';
        \File::isDirectory($requestPath) or \File::makeDirectory($requestPath);

        \File::put($requestPath.DIRECTORY_SEPARATOR.$resourceSingleName.'Request.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}'],
                [$this->resourceName,$resourceSingleName],
                __DIR__.'/stubs/Request.stub')
        );

    }

    protected function makeResourceFile(){

        \File::put($this->resourcePath.DIRECTORY_SEPARATOR.$this->resourceName.'Resource.php',
            $this->compileStub(
                '{resourceName}',
                $this->resourceName,
                __DIR__.'/stubs/Resource.stub')
        );

    }

    protected function makeResourceRoute($resourceSingleName) {

        \File::put($this->resourcePath.DIRECTORY_SEPARATOR.$this->resourceName.'Routes.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}', '{resourceNameLower}'],
                [$this->resourceName,$resourceSingleName, strtolower($this->resourceName)],
                __DIR__.'/stubs/Route.stub')
        );

    }

    protected function makeDatabaseFile($resourceSingleName){

        $databasePath = $this->resourcePath.DIRECTORY_SEPARATOR.'Database';
        \File::isDirectory($databasePath) or \File::makeDirectory($databasePath);

        $migrationPath = $databasePath.DIRECTORY_SEPARATOR.'migrations';
        \File::isDirectory($migrationPath) or \File::makeDirectory($migrationPath);

        $this->call('make:migration', [ 'name' => 'create_'.strtolower($this->resourceName).'_table', '--path' => str_replace(app_path(), "app", $migrationPath)]);


        $this->call('make:factory', [ 'name' => $resourceSingleName.'Factory', '--model' => $resourceSingleName]);
        $this->call('make:seeder', [ 'name' => $this->resourceName.'TableSeeder']);
    }

    
    protected function makeTestFile($resourceSingleName){

        $testPath = base_path('tests'.DIRECTORY_SEPARATOR.'Api'.DIRECTORY_SEPARATOR.'V1'.DIRECTORY_SEPARATOR.$resourceSingleName);
        \File::isDirectory($testPath) or \File::makeDirectory($testPath);

        \File::put($testPath.DIRECTORY_SEPARATOR.$resourceSingleName.'Test.php',
            $this->compileStub(
                ['{resourceName}','{resourceSingleName}', '{resourceNameLower}', '{resourceSingleNameLower}'],
                [$this->resourceName,$resourceSingleName, strtolower($this->resourceName), strtolower($resourceSingleName)],
                __DIR__.'/stubs/Test.stub')
        );

    }
}
