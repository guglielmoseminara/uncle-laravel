<?php
namespace UncleProject\UncleLaravel\Classes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;
use UncleProject\UncleLaravel\Classes\BaseRequestParser;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class RequestCriteria
 * @package Prettus\Repository\Criteria
 */
class BaseRequestCriteria implements CriteriaInterface
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    private function buildCondition(&$query, $field, $condition, $value, $or=false) {
        $valueArr = explode('-', $value);
        if (count($valueArr) > 1){
            if ($or) {
                $query->orWhereBetween($field, $valueArr);
            } else {
                $query->whereBetween($field, $valueArr);
            }
        } else {
            $valueOrArr = explode('|', $value);
            if(count($valueOrArr) > 0) {
                if ($or) {
                    $query->orWhere(function ($query) use ($field, $condition, $valueOrArr) {
                        foreach($valueOrArr as $index => $valueOr) {
                            if ($index == 0) {
                                $query->where($field, $condition, $valueOr);
                            } else {
                                $query->orWhere($field, $condition, $valueOr);
                            }
                        }
                    });
                } else {
                    $query->where(function ($query) use ($field, $condition, $valueOrArr) {
                        foreach($valueOrArr as $index => $valueOr) {
                            if ($index == 0) {
                                $query->where($field, $condition, $valueOr);
                            } else {
                                $query->orWhere($field, $condition, $valueOr);
                            }
                        }
                    });
                }
            } else {
                if ($or) {
                    $query->orWhere($field,$condition,$value);
                } else {
                    $query->where($field,$condition,$value);
                }
            }
        }
    }


    /**
     * Apply criteria in query repository
     *
     * @param         Builder|Model     $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     * @throws \Exception
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $fieldsSearchable = $repository->getFieldsSearchable();
        $search = $this->request->get(config('repository.criteria.params.search', 'search'), null);
        $searchFields = $this->request->get(config('repository.criteria.params.searchFields', 'searchFields'), null);
        $filter = $this->request->get(config('repository.criteria.params.filter', 'filter'), null);
        $orderBy = $this->request->get(config('repository.criteria.params.orderBy', 'orderBy'), null);
        $sortedBy = $this->request->get(config('repository.criteria.params.sortedBy', 'sortedBy'), 'asc');
        $with = $this->request->get(config('repository.criteria.params.with', 'with'), null);
        $searchJoin = $this->request->get(config('repository.criteria.params.searchJoin', 'searchJoin'), null);
        $sortedBy = !empty($sortedBy) ? $sortedBy : 'asc';
        if ($search && is_array($fieldsSearchable) && count($fieldsSearchable)) {
            
            $searchFields = is_array($searchFields) || is_null($searchFields) ? $searchFields : explode(';', $searchFields);

            $fields = BaseRequestParser::parserFieldsSearch($fieldsSearchable, $searchFields);
            $isFirstField = true;
            $searchData = BaseRequestParser::parserSearchData($search);
            $search = BaseRequestParser::parserSearchValue($search);
            $modelForceAndWhere = strtolower($searchJoin) === 'and';
            $model = $model->where(function ($query) use ($fields, $search, $searchData, $isFirstField, $modelForceAndWhere) {
                /** @var Builder $query */

                foreach ($fields as $field => $condition) {

                    if (is_numeric($field)) {
                        $field = $condition;
                        $condition = "=";
                    }
                    $value = null;
                    $condition = trim(strtolower($condition));
                    if (isset($searchData[$field])) {
                        $value = ($condition == "like" || $condition == "ilike") ? "%{$searchData[$field]}%" : $searchData[$field];
                    } else {
                        if (!empty($search)) {
                            $value = ($condition == "like" || $condition == "ilike") ? "%{$search}%" : $search;
                        }
                    }

                    $relation = null;
                    if(stripos($field, '.')) {
                        $explode = explode('.', $field);
                        $field = array_pop($explode);
                        $relation = implode('.', $explode);
                    }
                    $parentModel = $query->getModel();
                    $modelTableName = $this->getModelTableName($parentModel, $field);
                    if ( $isFirstField || $modelForceAndWhere ) {
                        if (!is_null($value)) {
                            if(!is_null($relation)) {
                                $relatedTable = $this->getRelatedTableName($parentModel, $relation, $field);
                                $query->whereHas($relation, function($query) use($field,$condition,$value, $relatedTable) {
                                    $this->buildCondition($query, $relatedTable.'.'.$field, $condition, $value);
                                });
                            } else {
                                $this->buildCondition($query, $modelTableName.'.'.$field, $condition, $value);
                            }
                            $isFirstField = false;
                        }
                    } else {
                        if (!is_null($value)) {
                            if(!is_null($relation)) {
                                $relatedTable = $this->getRelatedTableName($parentModel, $relation, $field);
                                $query->orWhereHas($relation, function($query) use($field,$condition,$value,$relatedTable) {
                                    $this->buildCondition($query, $relatedTable.'.'.$field, $condition, $value);
                                });
                            } else {
                                $this->buildCondition($query, $modelTableName.'.'.$field, $condition, $value);
                            }
                        }
                    }
                }
            });
        }

        if (isset($orderBy) && !empty($orderBy)) {
            $orders = explode('|', $orderBy);
            $sorters = explode('|', $sortedBy);
            foreach($orders as $index => $order)
            {
                if(stripos($order, '.')) {
                    $explode = explode('.', $order);
                    $field = array_pop($explode);
                    $relation = implode('.', $explode);
                }
                if($relation) {
                    $modelInstance = $model->getModel();
                    $table = $modelInstance->getTable();
                    if (method_exists($modelInstance, 'getJoinField')) {
                        $field = $model->getModel()->getJoinField($order, $sorters[$index]);
                        list($relatedTable, $relatedId) = explode('.', $field);
                        $relationKey = $model->getModel()->$relation()->getModel()->getKeyName();
                        $model = $model->leftJoin($relatedTable, "$table.id", '=', "$relatedTable.$relationKey")->orderBy($field, $sorters[$index]);
                    }
                } else {
                    $model = $model->orderBy($order, $sorters[$index]);
                }
            }
            
        }

        if (isset($filter) && !empty($filter)) {
            if (is_string($filter)) {
                $filter = explode(';', $filter);
            }

            $model = $model->select($filter);
        }
        if ($with) {
            $with = explode(';', $with);
            $model = $model->with($with);
        }
        return $model;
    }

    protected function getModelTableName($model, $field) {
        $modelTableName = $model->getTable();
        if (method_exists($model, 'translationModel')) {
            $translationModel = $model->translationModel();
            if (isset($model->translatable) && is_array($model->translatable) && in_array($field, $model->translatable)) {
                $modelTableName = $translationModel->getTable();
            }
        }
        return $modelTableName;
    }

    protected function getRelatedTableName($model, $relation, $field) {
        $relationships = explode('.', $relation);
        $relationObject = $model;
        $lastRelation = null;
        foreach($relationships as $key => $relationship) {
            $relationObject = $relationObject->$relationship();
            $lastRelation = $relationObject;
            if ($key < (count($relationships) - 1)) {
                $relationObject = $relationObject->getRelated();
            }
        }
        if ($lastRelation instanceof BelongsToMany) {
            $relatedTable = $relationObject->getTable();
            $translationTable = $this->getTranslatableTable($relationObject->getModel(), $field);
        } else if ($lastRelation instanceof HasMany || $lastRelation instanceof BelongsTo || $lastRelation instanceof HasOne) {
            $relatedTable = $relationObject->getRelated()->getTable();
            $translationTable = $this->getTranslatableTable($relationObject->getRelated(), $field);
        }
        else {
            $relatedTable = $relationObject->getTable();
            $translationTable = $this->getTranslatableTable($relationObject->getModel(), $field);
        }
        $relatedTable = $translationTable ? $translationTable : $relatedTable;
        return $relatedTable;
    }

    protected function getTranslatableTable($model, $field) {
        if ($model->translatable && in_array($field, $model->translatable)) {
            return $model->translationModel()->getTable();
        }
    }
}
