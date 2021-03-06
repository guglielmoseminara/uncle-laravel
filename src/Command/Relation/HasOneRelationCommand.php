<?php

namespace UncleProject\UncleLaravel\Command\Relation;

class HasOneRelationCommand extends BaseRelationCommand
{


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uncle:relation:HasOne {resourceParent} {modelParent} {resourceChild} {modelChild}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a One to One relation between two model';

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
            $this->argument('resourceChild'),
            $this->argument('modelChild'));

        $error = $this->checkActor();

        if($error['error']) {
            $this->error($error['message']);
            return;
        }

        $error = $this->addRelation('HasOne');

        if($error['error']) {
            $this->error($error['message']);
            return;
        }

        if($this->hasOption('inverse')){
            $error = $this->addRelation('BelongsTo', $this->modelChildPath, $this->resourceParent, $this->modelParent);

            if($error['error']) {
                $this->error($error['message']);
                return;
            }
        }

        $this->info("Relation HasOne between $this->modelParent and $this->modelChild successful created");
    }






}
