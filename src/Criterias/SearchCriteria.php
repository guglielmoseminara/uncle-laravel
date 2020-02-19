<?php

namespace UncleProject\UncleLaravel\Criterias;

use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Contracts\CriteriaInterface;

class SearchCriteria implements CriteriaInterface {

    private $request;

    public function __construct($request) {
        $this->request = $request;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        if (isset($this->request->categories) && count($this->request->categories) > 0){
            $model = $model->where(function ($model){
                foreach($this->request->categories as $index => $category) {
                    if ($index == 0) {
                        $model->where('category_id', '=', $category);
                    } else {
                        $model->orWhere('category_id', '=', $category);
                    }
                }
            });
        }
        if (isset($this->request->cities) && count($this->request->cities) > 0){
            $model = $model->where(function ($model){
                foreach($this->request->cities as $index => $city) {
                    if ($index == 0) {
                        $model->where('city_id', '=', $city);
                    } else {
                        $model->orWhere('city_id', '=', $city);
                    }
                }
            });
        }
        if (isset($this->request->pois) && count($this->request->pois) > 0) {
            $model = $model->whereHas('pois', function($model) {
                foreach($this->request->pois as $index => $poi) {
                    if ($index == 0) {
                        $model->where('poi_id', '=', $poi);
                    } else {
                        $model->orWhere('poi_id', '=', $poi);
                    }
                }
            });
        }
        if (isset($this->request->tags) && count($this->request->tags) > 0) {
            $model = $model->whereHas('tags', function($model) {
                foreach($this->request->tags as $index => $tag) {
                    if ($index == 0) {
                        $model->where('tag_id', '=', $tag);
                    } else {
                        $model->orWhere('tag_id', '=', $tag);
                    }
                }
            });
        }
        if (isset($this->request->price)) {
            $model = $model->whereBetween('price', $this->request->price);
        }
        if (isset($this->request->duration)) {
            $model = $model->whereBetween('duration', $this->request->duration);
        }
        $model = $model->where('isSuspended', '=', 0);
        return $model;
    }
}
