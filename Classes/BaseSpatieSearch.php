<?php

namespace UncleProject\UncleLaravel\Classes;

use Spatie\Searchable\Search;
use UncleProject\UncleLaravel\Classes\BaseSearchAspect;
use Illuminate\Support\Arr;

class BaseSpatieSearch extends Search {

    public function registerModel(string $modelClass, ...$attributes): Search
    {
        if (isset($attributes[0]) && is_callable($attributes[0])) {
            $attributes = $attributes[0];
        }

        if (is_array(Arr::get($attributes, 0))) {
            $attributes = $attributes[0];
        }

        $searchAspect = new BaseSearchAspect($modelClass, $attributes);

        $this->registerAspect($searchAspect);

        return $this;
    }

}
