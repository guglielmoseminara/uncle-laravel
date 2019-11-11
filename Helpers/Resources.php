<?php

namespace UncleProject\UncleLaravel\Helpers;
use App;

class Resources {

    public function __construct() {
    }

    public function getResource($resource) {
        $resource_classname = App::make('Utils')->getResourcesNamespace()."\\$resource\\$resource"."Resource";
        return new $resource_classname();
    }

}
