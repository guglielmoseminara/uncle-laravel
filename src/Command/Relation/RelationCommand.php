<?php

namespace UncleProject\UncleLaravel\Command;

use UncleProject\UncleLaravel\Classes\BaseCommand;

class RelationCommand extends BaseCommand
{

    private $parentName;
    private $parentSingleName;
    private $parentPath;
    private $childName;
    private $childSingleName;
    private $childPath;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'relation:create {parent} {relation} {child} {pivot?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a relation between two model';

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

        $names = $this->resolveResourceName($this->argument('parent'));
        $this->parentSingleName = $names['singular'];
        $this->parentName = $names['plural'];
        $this->parentPath = app_path('Http'.DIRECTORY_SEPARATOR.'Resources'). DIRECTORY_SEPARATOR. $this->parentName;

        $this->resolveResourceName($this->argument('relation'));

        $names = $this->resolveResourceName($this->argument('child'));
        $this->childSingleName = $names['singular'];
        $this->childName = $names['plural'];
        $this->childPath = app_path('Http'.DIRECTORY_SEPARATOR.'Resources'). DIRECTORY_SEPARATOR. $this->childName;

        if (!\File::exists($this->parentPath) || !\File::exists($this->childPath)) {
            $this->error('Resources not exists');
            return;
        }

        if (!in_array($this->argument('relation'), ['HasOne', 'HasMany', 'belongsToMany']) || !\File::exists($this->childPath)) {
            $this->error('Resources not exists');
            return;
        }
        $this->resolveRelation($this->argument('relation'));


    }

    private function resolveRelation(){

    }





}
