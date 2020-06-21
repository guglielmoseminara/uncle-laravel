<?php

namespace UncleProject\UncleLaravel\Command;

use UncleProject\UncleLaravel\Classes\BaseCommand;
use Illuminate\Support\Facades\Artisan;

class XMLCompileCommand extends BaseCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uncle:generate-xml';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the unique config xml file from xml of resources';

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
        $this->callSilent('config:cache', [ '--env' => 'local']);

        $xmlFile = $this->resourcesPath.DIRECTORY_SEPARATOR.'api.uncle.xml';
        if (!\File::exists($xmlFile)) {
            \File::delete($xmlFile);
        }

        \File::put($xmlFile,
            '<uncle>'.PHP_EOL.'</uncle>'
        );


        if(!empty(config('uncle.resources'))){
            foreach(config('uncle.resources') as $name => $resourcePath){
                $xmlResourceFile = $this->resourcesPath.DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR.$name.'.uncle.xml';
                if (\File::exists($xmlResourceFile)) {
                    $this->writeInFile($xmlFile, '<uncle>', \File::get($xmlResourceFile));
                }
            }
        }
        else $this->info("Command array to run is empty! Enter new commands to run");

    }





}
