<?php

namespace UncleProject\UncleLaravel\Criterias;

use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Contracts\CriteriaInterface;
use UncleProject\UncleLaravel\Classes\BaseRequestParser;
use App;
use Auth;

class MorphSearchCriteria implements CriteriaInterface {

    private $search;

    protected $morphIdKey;
    protected $morphTypeKey;
    protected $morphMap;

    public function __construct($searchField) {
        $this->search = $searchField;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        if($this->search) {
            $search = BaseRequestParser::parserSearchData($this->search);
            $key = array_keys($search)[0];
            if(key_exists($key, $this->morphMap)){
                $model = $model->where($this->morphIdKey, $search[$key])
                    ->where($this->morphTypeKey, App::make($this->morphMap[$key]['resource'])->getModelClassPath($this->morphMap[$key]['model']));
            }
        }

        return $model;
    }
}
