<?php

namespace UncleProject\UncleLaravel\Command\Resource;


class GenerateCommand extends BaseResourceCommand
{



    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource:generate {resource} {--in=} ';

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

        if ($this->option('in')) {
            $this->resourceName = $this->option('in');
            $this->resourcePath = $this->resourcesPath. DIRECTORY_SEPARATOR. $this->resourceName;

            if (!\File::exists($this->resourcePath)) {
                $this->error($this->option('in') . ' resource not exists! ');
                return;
            }
        }
        else {

            $this->resourceName = $names['plural'];
            $this->resourcePath = $this->resourcesPath. DIRECTORY_SEPARATOR. $this->resourceName;

            if (\File::exists($this->resourcePath)) {
                $this->error($this->resourceName  . ' resource already exists');
                return;
            }
        }


        if(!$this->option('in')){
            $this->addInConfig();

            \File::makeDirectory($this->resourcePath);

            $this->makeResourceFile();
            $this->makeResourceRoute($names['singular']);
        }

        $this->makeResourceControllers($names['singular']);
        $this->makeResourceFakers($names['singular']);
        $this->makeResourceModels($names['singular']);
        $this->makeResourcePresenters($names['singular']);
        $this->makeResourceRepositories($names['singular']);
        $this->makeResourceRequests($names['singular']);

        $this->makeDatabaseFile($names['singular']);
        $this->makeTestFile($names['singular']);

        if($this->option('in')) $this->info("Classes {$names['singular']} generate successfully in Resource {$this->resourceName}");
        else $this->info("Resource {$this->resourceName} generate successfully");
    }
}
