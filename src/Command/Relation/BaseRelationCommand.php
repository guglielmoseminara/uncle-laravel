<?php

namespace UncleProject\UncleLaravel\Command\Relation;

use UncleProject\UncleLaravel\Classes\BaseCommand;

class BaseRelationCommand extends BaseCommand
{
    protected $resourceParent;
    protected $modelParent;
    protected $resourceChild;
    protected $modelChild;

    protected function resolveRelationActorName($inputResourceParent, $inputModelParent, $inputResourceChild, $inputModelChild){

        $this->resourceParent = $this->resolveResourceName($inputResourceParent)['plural'];
        $this->modelParent    = $this->resolveModelName($inputModelParent);
        $this->resourceChild  = $this->resolveResourceName($inputResourceChild)['plural'];
        $this->modelChild     = $this->resolveModelName($inputModelChild);
    }

    protected function checkActor(){

        $this->resourcePath = $this->resourcesPath. DIRECTORY_SEPARATOR. $this->resourceName;

        if (\File::exists($this->resourcePath)) {
            $this->error($this->resourceName  . ' resource already exists');
            return;
        }
    }
}
