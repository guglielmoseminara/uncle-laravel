<?php

namespace UncleProject\UncleLaravel\Command\Relation;

use UncleProject\UncleLaravel\Classes\BaseCommand;
use Symfony\Component\Console\Input\InputOption;


class BaseRelationCommand extends BaseCommand
{
    protected $resourceParent;
    protected $modelParent;
    protected $resourceChild;
    protected $modelChild;

    protected $resourceParentPath;
    protected $modelParentPath;
    protected $resourceChildPath;
    protected $modelChildPath;

    protected $relations = ['HasOne', 'HasMany', 'BelongsTo', 'BelongsToMany', 'MorphOne', 'MorphMany', 'MorphTo', 'MorphToInverse'];

    protected $morphKey;

    public function __construct()
    {
        parent::__construct();

        $this->getDefinition()->addOptions([new InputOption('inverse')]);
    }

    protected function resolveRelationActorName($inputResourceParent, $inputModelParent, $inputResourceChild, $inputModelChild){

        $this->resourceParent = $this->resolveResourceName($inputResourceParent)['plural'];
        $this->modelParent    = $this->resolveModelName($inputModelParent);
        $this->resourceChild  = $this->resolveResourceName($inputResourceChild)['plural'];
        $this->modelChild     = $this->resolveModelName($inputModelChild);
    }

    protected function checkActor(){

        $this->resourceParentPath = $this->resourcesPath. DIRECTORY_SEPARATOR. $this->resourceParent;

        if (!\File::exists($this->resourceParentPath)) {
            return [
                'error' => true,
                'message' => "$this->resourceParent resource not exists"
            ];
        }

        $this->modelParentPath = $this->resourceParentPath. DIRECTORY_SEPARATOR. 'Models' . DIRECTORY_SEPARATOR . $this->modelParent .'.php';

        if (!\File::exists($this->modelParentPath)) {
            return [
                'error' => true,
                'message' => "$this->modelParent model not exists"
            ];
        }

        $this->resourceChildPath = $this->resourcesPath. DIRECTORY_SEPARATOR. $this->resourceChild;

        if (!\File::exists($this->resourceChildPath)) {
            return [
                'error' => true,
                'message' => "$this->resourceChild resource not exists"
            ];
        }

        $this->modelChildPath = $this->resourceChildPath. DIRECTORY_SEPARATOR. 'Models' . DIRECTORY_SEPARATOR . $this->modelChild .'.php';

        if (!\File::exists($this->modelChildPath)) {
            return [
                'error' => true,
                'message' => "$this->modelChild model not exists"
            ];
        }

        return ['error' => false];

    }


    protected function addRelation($relation, $modelPath = null, $resource = null, $model = null){

        if(in_array($relation, $this->relations)) {
            $modelPath = ($modelPath) ? $modelPath : $this->modelParentPath;
            $resource = ($resource) ? $resource : $this->resourceChild;
            $model = ($model) ? $model : $this->modelChild;

            $functionName = lcfirst($model);
            if(in_array($relation, ['HasMany','BelongsToMany','MorphMany']))
                $functionName = str_plural($functionName);

            if(!$this->fileExistFunction($modelPath, $functionName))
            {
                if(in_array($relation, ['MorphOne','MorphMany', 'MorphToInverse'])){
                    $arraySearch = ['{resource}', '{model}', '{functionName}', '{morphKey}'];
                    $arrayReplace = [$resource, $model, $functionName, $this->morphKey];
                }
                else {
                    $arraySearch = ['{resource}', '{model}', '{functionName}'];
                    $arrayReplace = [$resource, $model, $functionName];
                }

                $this->writeInFile(
                    $modelPath,
                    '//Add Relations - Uncle Comment (No Delete)',
                    $this->compileStub(
                        $arraySearch ,
                        $arrayReplace,
                        __DIR__."/stubs/$relation.stub")
                );

                return ['error' => false];
            }
            else return [
                'error'   => true,
                'message' => "Function $functionName already exist in $modelPath"
            ];
        }
        else return [
            'error'   => true,
            'message' => "$relation is not an available Relation"
        ];
    }
}
