<?php

namespace UncleProject\UncleLaravel\Classes;
use App;

class BaseResource {

    public function getModelClassPath($model) {
        return App::make('Utils')->getResourcesNamespace()."\\".ucfirst($this->name)."\\Models\\".ucfirst($model);
    }

    public function getRepositoryClassPath($repository) {
        return App::make('Utils')->getResourcesNamespace()."\\".ucfirst($this->name)."\\Repositories\\".ucfirst($repository)."Repository";
    }

    public function getServiceClassPath($service) {
        return App::make('Utils')->getResourcesNamespace()."\\".ucfirst($this->name)."\\Services\\".ucfirst($service)."Service";
    }

    public function getFakerClassPath($service) {
        return App::make('Utils')->getResourcesNamespace()."\\".ucfirst($this->name)."\\Fakers\\".ucfirst($service)."Faker";
    }

    public function getControllerClassPath($controller) {
        return App::make('Utils')->getResourcesNamespace()."\\".ucfirst($this->name)."\\Controllers\\".strtoupper(config('api.version'))."\\$controller"."Controller";
    }

    public function getPresenterClassPath($presenter) {
        return App::make('Utils')->getResourcesNamespace()."\\".ucfirst($this->name)."\\Presenters\\".ucfirst($presenter)."Presenter";
    }

    public function getRepository($resource) {
        $classPath = $this->getRepositoryClassPath($resource);
        return App::make($classPath);
    }

    public function getService($service) {
        $classPath = $this->getServiceClassPath($service);
        return App::make($classPath);
    }

    public function getFaker($faker) {
        $classPath = $this->getFakerClassPath($faker);
        return App::make($classPath);
    }

    public function getModelInstance($model) {
        return App::make($this->getModelClassPath($model));
    }
}
