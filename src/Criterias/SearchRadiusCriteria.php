<?php

namespace UncleProject\UncleLaravel\Criterias;

use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Contracts\CriteriaInterface;
use UncleProject\UncleLaravel\Classes\BaseRequestParser;
use Illuminate\Support\Facades\DB;

class SearchRadiusCriteria implements CriteriaInterface {

    private $searchField;
    private $distance;


    public function __construct($searchField, $distance = 200) {
        $this->searchField = $searchField;
        $this->distance  = $distance;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        if ($this->searchField) {
            $search = BaseRequestParser::parserSearchData($this->searchField);
            if (isset($search['latitude']) && isset($search['longitude'])) {
                $latitude = $search['latitude'];
                $longitude = $search['longitude'];
                $table = $model->getModel()->getTable();
                $sql_distance = "(((acos(sin((" . $latitude . "*pi()/180)) * sin((`" . $table . "`.`latitude`*pi()/180))+cos((" . $latitude . "*pi()/180)) * cos((`" . $table . "`.`latitude`*pi()/180)) * cos(((" . $longitude . "-`" . $table . "`.`longitude`)*pi()/180))))*180/pi())*60*1.1515*1.609344)";
                $model = $model->where(DB::raw($sql_distance), '<=', $this->distance);
            }
        }
        return $model;
    }
}
