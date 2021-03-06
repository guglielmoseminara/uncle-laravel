<?php

namespace UncleProject\UncleLaravel\Command\Resource;



class NotificationCommand extends BaseResourceCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uncle:resource:create-notification {resource} {notification}';

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
        $this->resourceName = $this->argument('resource');

        $this->resourcePath = app_path('Http'.DIRECTORY_SEPARATOR.'Resources'). DIRECTORY_SEPARATOR. $this->resourceName;

        if (!\File::exists($this->resourcePath)) {
            $this->error($this->resourceName  . ' resource not exists');
            return;
        }

        $this->makeResourceNotifications($this->argument('notification'));

        $this->info("Notification {$this->argument('notification')} in Resource {$this->resourceName} generate successfully");
    }


    protected function makeResourceNotifications($notificationName){

        $notificationsPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Notifications';
        \File::isDirectory($notificationsPath) or\File::makeDirectory($notificationsPath);

        \File::put(
            $notificationsPath.DIRECTORY_SEPARATOR.$notificationName.'Notification.php',
            $this->compileStub(
                ['{resourceName}','{notificationName}'],
                [$this->resourceName,$notificationName],
                __DIR__.'/stubs/Notification.stub')
        );

        $notificationsViewsPath = $this->resourcePath.DIRECTORY_SEPARATOR.'Notifications'.DIRECTORY_SEPARATOR.'mails';
        \File::isDirectory($notificationsViewsPath) or \File::makeDirectory($notificationsViewsPath);

        \File::put(
            $notificationsViewsPath.DIRECTORY_SEPARATOR.$notificationName.'Mail.blade.php',
            $this->compileStub(
                ['{resourceName}','{notificationName}'],
                [$this->resourceName, $notificationName],
                __DIR__.'/stubs/Mail.stub')
        );
    }


}
