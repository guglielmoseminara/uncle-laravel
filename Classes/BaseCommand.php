<?php

namespace UncleProject\UncleLaravel\Classes;

use Illuminate\Console\Command;

class BaseCommand extends Command
{
    protected function resolveResourceName($input){
        $resourceName = ucfirst($input);

        $names = [
            'singular' => '',
            'plural' => '',
        ];

        $names['singular']  = str_singular($resourceName);

        if($names['singular'] == $resourceName){
            $names['plural'] = str_plural($names['singular']);
        }

        return $names;
    }

    protected function compileStub($searchs,$replaces,$stubPath){
        return str_replace($searchs,$replaces,\File::put($stubPath));
    }

    protected function writeInFile($filename, $search, $insert){
        \File::put($filename, $this->compileStub($search, $search. "\n\n". $insert, \File::get($filename)));
    }
}