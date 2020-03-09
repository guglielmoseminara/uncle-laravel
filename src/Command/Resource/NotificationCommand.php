<?php

namespace UncleProject\UncleLaravel\Command\Resource;

use UncleProject\UncleLaravel\Command\Resource\BaseResourceCommand;

class NotificationCommand extends BaseResourceCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource:create-notification {resource} {notification}';

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

        if (!\File::exists($this->resourcePath)) {
            $this->error($this->resourceName  . ' resource not exists');
            return;
        }

        $this->makeResourceNotifications($this->argument('notification'));

    }





}
