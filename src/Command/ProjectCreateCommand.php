<?php

namespace UncleProject\UncleLaravel\Command;

use UncleProject\UncleLaravel\Classes\BaseCommand;
use Illuminate\Support\Facades\Artisan;

class ProjectCreateCommand extends BaseCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uncle:generate-project';

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
        if(!empty(config('uncle.project_commands'))){
            foreach(config('uncle.project_commands') as $command){
                Artisan::call($command);
                $this->info(Artisan::output());
            }
        }
        else $this->info("Command array to run is empty! Enter new commands to run");

    }





}
