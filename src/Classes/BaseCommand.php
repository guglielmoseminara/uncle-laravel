<?php

namespace UncleProject\UncleLaravel\Classes;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BaseCommand extends Command
{

    protected $resourcesPath;


    public function __construct()
    {
        $this->resourcesPath = app_path('Http'.DIRECTORY_SEPARATOR.'Resources');
        parent::__construct();
    }


    protected function resolveResourceName($input){
        $resourceName = ucfirst($input);

        $names = [
            'singular' => Str::singular($resourceName),
            'plural' => Str::plural($resourceName),
        ];

        if($names['singular'] == $names['plural']){
            $names['plural'] = Str::plural($names['singular']);
        }

        return $names;
    }

    protected function resolveModelName($input){

        $names = $this->resolveResourceName($input);

        return $names['singular'];
    }

    protected function compileStub($searchs,$replaces,$stubPath){
        return str_replace($searchs,$replaces,\File::get($stubPath));
    }

    protected function fileExistFunction($filePath, $function){
        return (strpos(\File::get($filePath), "public function $function()") !== false);
    }

    protected function writeInFile($filename, $search, $insert){
        \File::put($filename, $this->compileStub($search, $search. "\n". $insert, $filename));
    }
}