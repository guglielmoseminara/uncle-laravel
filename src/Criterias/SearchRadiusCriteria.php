<?php

namespace UncleProject\UncleLaravel\Criterias;

use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Contracts\CriteriaInterface;
use UncleProject\UncleLaravel\Classes\BaseRequestParser;

class SearchRadiusCriteria implements CriteriaInterface {

    private $searchField;
    private $distance;


    public function __construct($searchField, $distance = 20) {
        $this->searchField = $searchField;
        $this->distance  = $distance;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        if ($this->searchField) {
            $search = BaseRequestParser::parserSearchData($this->searchField);
            $latitude = $search['latitude'];
            $longitude = $search['longitude'];
            if (isset($latitude) && isset($longitude)) {
                $table = $model->getTable();
                $sql_distance = "(((acos(sin((" . $latitude . "*pi()/180)) * sin((`" . $table . "`.`latitude`*pi()/180))+cos((" . $latitude . "*pi()/180)) * cos((`" . $table . "`.`latitude`*pi()/180)) * cos(((" . $longitude . "-`" . $table . "`.`longitude`)*pi()/180))))*180/pi())*60*1.1515*1.609344) as distance ";
                $model = $model->addSelect($sql_distance)
                    ->where('distance', '<=', $this->distance);
            }
        }
        return $model;
    }
}
