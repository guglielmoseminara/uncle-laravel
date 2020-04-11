<?php

namespace UncleProject\UncleLaravel\Classes;

use Prettus\Repository\Eloquent\BaseRepository as EloquentBaseRepository;
use App;

class BaseRepository extends EloquentBaseRepository {

    public function boot() {
        $this->pushCriteria(app('\UncleProject\UncleLaravel\Classes\BaseRequestCriteria'));

        $xml = App::make('XMLResource',['resource' => $this->resourceName]);
        if($xml->hasXML()){
            $searchables = $xml->getRepositorySearchable('Test');

            foreach ($searchables as $searchable){
                if(isset($searchable->attributes()['option']))
                    $this->fieldSearchable[$searchable->attributes()['name']->__toString()] = $searchable->attributes()['option']->__toString();
                else array_push($this->fieldSearchable,$searchable->attributes()['name']->__toString());
            }
        }
    }

    public function resource() {
        return App::make(ucfirst($this->resourceName).'Resource');
    }

    public function model() {
        return $this->resource()->getModelClassPath($this->modelName);
    }

    public function modelInstance() {
        return App::make($this->model());
    }

    public function getModelInstance() {
        return App::make($this->model());
    }

    private function withPresenter($presenter) {
        $presenterClass = $this->resource()->getPresenterClassPath(ucfirst($presenter));
        $this->setPresenter($presenterClass);
    }

    public function findWithPresenter($id, $presenter) {
        $utils = App::make('Utils');
        $this->withPresenter($presenter);
        return $utils->toObject($this->find($id))->data;
    }

    public function findByFieldWithPresenter($field, $value, $presenter) {
        $utils = App::make('Utils');
        $this->withPresenter($presenter);
        return $utils->toObject($this->findByField($field, $value))->data;
    }

    public function firstWithPresenter($presenter) {
        $utils = App::make('Utils');
        $this->withPresenter($presenter);
        return $utils->toObject($this->first())->data;
    }


    public function getRelationship($relationship) {
        try {
            $paths = explode('\\', get_class($this->modelInstance()->{$relationship}()));
        } catch(\Exception $e) {
            return null;
        }
        return end($paths);
    }


    public function withTrashed() {
        $this->model = $this->model->withTrashed();
        return $this;
    }

}
