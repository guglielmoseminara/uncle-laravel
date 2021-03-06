<?php

namespace UncleProject\UncleLaravel\Helpers;

use PhpParser\ErrorHandler\Collecting;
use League\Fractal\Manager;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Item;
use stdClass;


class Utils {

    public function construct($app) {
    }

    public function getResourcesNamespace() {
        return "App\\Http\\Resources";
    }

    public function isAssoc(array $arr) {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function toObject($array) {
        if ($this->isAssoc($array)) {
            $obj = new stdClass();
            foreach ($array as $key => $val) {
                $obj->$key = is_array($val) ? $this->toObject($val) : $val;
            }
        } else {
            $obj = [];
            foreach ($array as $key => $val) {
                $obj[$key] = is_array($val) ? $this->toObject($val) : $val;
            }
        }
        return $obj;
    }

    public function transform($items, $presenter) {
        if(!$items) return $items;
        $presenterObj = new $presenter();
        $results = array_map(array($this, 'toObject'), collect($items)->transformWith($presenterObj->getTransformer())->toArray()['data']);
        return $results;
    }

    public function transformItem($item, $presenter) {
        if(!$item) return $item;
        $presenterObj = new $presenter();
        return $presenterObj->getTransformer()->transform($item);
    }

    public function hoursToMinutes($hours) {
        $minutes = 0;
        if (strpos($hours, ':') !== false) {
            list($hours, $minutes) = explode(':', $hours);
        }
        return $hours * 60 + $minutes;
    }

    public function minutesToHours($time, $format = '%02d:%02d') {
        if ($time < 1) {
            return;
        }
        $hours = floor($time / 60);
        $minutes = ($time % 60);
        return sprintf($format, $hours, $minutes);
    }

    public function paginateForApi($collection, $presenter, $perPage = 15){

        $paginator = $collection->paginate($perPage);
        $collection = $paginator->getCollection();

        $resource = new Collection($collection, $presenter->getTransformer());
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));

        $manager = new Manager();
        $manager->setSerializer(new DataArraySerializer());

        return $manager->createData($resource)->toArray();

    }


}
