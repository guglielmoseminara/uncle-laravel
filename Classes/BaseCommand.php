<?php

namespace UncleProject\UncleLaravel\Classes;

use Illuminate\Console\Command;

class BaseCommand extends Command
{

    protected function compileStub($searchs,$replaces,$stubPath)
    {

        return str_replace($searchs,$replaces,file_get_contents($stubPath));
    }
}