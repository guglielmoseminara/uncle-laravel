<?php

namespace UncleProject\UncleLaravel\Command\Relation;


class MorphManyRelationCommand extends BaseRelationCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'relation:MorphMany {resourceParent} {modelParent} {resourceMorph} {modelMorph} {morphKey}';

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

        $this->resolveRelationActorName(
            $this->argument('resourceParent'),
            $this->argument('modelParent'),
            $this->argument('resourceMorph'),
            $this->argument('modelMorph'));

        $error = $this->checkActor();

        if($error['error']) {
            $this->error($error['message']);
            return;
        }

        $this->morphKey = $this->argument('morphKey');

        $this->addRelation('MorphMany');

        if($this->hasOption('inverse')){
            $this->addRelation('MorphToInverse', $this->modelChildPath, $this->resourceParent, $this->modelParent);
        }

        $this->info("Relation MorphOne between $this->modelParent and $this->modelChild successful created");


    }

}
