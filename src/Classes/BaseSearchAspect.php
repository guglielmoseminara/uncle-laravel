<?php

namespace UncleProject\UncleLaravel\Classes;

use Spatie\Searchable\Exceptions\InvalidModelSearchAspect;
use Spatie\Searchable\ModelSearchAspect;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Arr;
use App;
use Schema;

class BaseSearchAspect extends ModelSearchAspect {

    protected $conditions = [];
    protected $groupby = [];
    protected $scopes = [];
    protected $filter_text = null;

    public function __construct($model, $attributes) {
        $newAttributes = [];
        $conditions = [];
        foreach ($attributes as $key => $attribute) {
            if (!is_array($attribute)) {
                $newAttributes[] = $attribute;
            } else {
                if (count($attribute) > 0) {
                    if (isset($attribute['group_by'])) {
                        $this->groupby = $attribute['group_by'];
                    } 
                    if (isset($attribute['scopes'])) {
                        $this->scopes = $attribute['scopes'];
                    }
                    if (isset($attribute['filter_text'])) {
                        $this->filter_text = $attribute['filter_text'];
                    }
                    foreach ($attribute as $k => $value) {
                        if (!in_array($k, ['group_by', 'scopes', 'filter_text'])) {
                            $conditions[$k] = $value;
                        }
                    }
                }
            }
        }
        $this->conditions = $conditions;
        parent::__construct($model, $newAttributes);
    }

    public function getResults(string $term, User $user = null): Collection
    {
        $query = ($this->model)::query();

        $filteredTerm = $this->filterText($term);
        $this->addSearchConditions($query, $filteredTerm);
        $this->addSearchScopes($query, $filteredTerm, $term);
        return $query->take(10)->get();
    }

    public function addSearchConditions(Builder $query, string $term)
    {
        $attributes = $this->attributes;
        $searchTerms = [$term];
        $query->where(function (Builder $query) use ($attributes, $term, $searchTerms) {
            foreach (Arr::wrap($attributes) as $attribute) {
                foreach ($searchTerms as $searchTerm) {
                    $fieldSplit = explode('.', $attribute->getAttribute());
                    $searchTerm = mb_strtolower($searchTerm, 'UTF8');
                    if (count($fieldSplit) > 1) {
                        $sql = "";
                        $searchTerm = str_replace(' ', '%', $searchTerm);
                        $sql = "(LOWER({$fieldSplit[1]}) LIKE ?)";
                        $query->orWhereHas($fieldSplit[0], function($query) use ($sql, $searchTerm) {
                            $query->whereRaw($sql, ["%{$searchTerm}%"]);
                        });
                    } else {
                        $searchTerm = str_replace(' ', '%', $searchTerm);
                        $sql = "(LOWER({$attribute->getAttribute()}) LIKE ?)";
                        $query->orWhereRaw($sql, ["%{$searchTerm}%"]);
                    }
                }
            }
        });
        if (count($this->conditions) > 0) {
            foreach ($this->conditions as $key => $value) {
                $relation = null;
                if(stripos($key, '.')) {
                    $explode = explode('.', $key);
                    $key = array_pop($explode);
                    $relation = implode('.', $explode);
                }
                if (strstr($value, '!') !== false) {
                    $condition = '!=';
                    $value = str_replace('!', '', $value);    
                } else {
                    $condition = '=';
                }
                if(!is_null($relation) && method_exists($query->getModel(), $relation)) {
                    $query->whereHas($relation, function($query) use($key, $condition, $value) {
                        $query->where($key, $condition, $value);
                    });
                } else {
                    if (Schema::hasColumn(App::make($this->model)->getTable(), $key)) {
                        $query->where($key, $condition, $value);
                    }
                }
            }
        }
        if (count($this->groupby) > 0) {
            $query->groupby($this->groupby[1]);
        }
    }

    public function addSearchScopes(Builder $query, $filteredTerm, $term) {
        if (count($this->scopes) > 0) {
            foreach($this->scopes[1] as $kscope => $vscope) {
                call_user_func([$query, $vscope], $filteredTerm, $term);
            }
        }
    }

    public function filterText($term) {
        if ($this->filter_text) {
            foreach($this->filter_text[1] as $kfilter => $vfilter) {
                $term = call_user_func($vfilter, $term);
            }
        }
        return $term;
    }


}
