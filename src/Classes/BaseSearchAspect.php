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

    public function __construct($model, $attributes) {
        $newAttributes = [];
        $conditions = [];
        foreach ($attributes as $key => $attribute) {
            if (!is_array($attribute)) {
                $newAttributes[] = $attribute;
            } else {
                if (count($attribute) > 0) {
                    if (array_values($attribute)[0] == 'group_by') {
                        $this->groupby = array_values($attribute)[1];
                    } 
                    else if (array_values($attribute)[0] == 'scopes') {
                        $this->scopes = array_values($attribute)[1];
                    }
                    else {
                        foreach ($attribute as $k => $value) {
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
        if (empty($this->attributes)) {
            throw InvalidModelSearchAspect::noSearchableAttributes($this->model);
        }

        $query = ($this->model)::query();

        $this->addSearchConditions($query, $term);
        $this->addSearchScopes($query);

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
                        $sql = "MATCH({$fieldSplit[1]}) AGAINST(?)";
                        $attribute->isPartial()
                            ? $query->orWhereHas($fieldSplit[0], function($query) use ($sql, $searchTerm) {
                                $query->whereRaw($sql, [$searchTerm]);
                            })
                            : $query->orWhereHas($fieldSplit[0], function($query) use ($sql, $searchTerm, $attribute) {
                                $query->where($attribute->getAttribute(), $searchTerm);
                            });         
                    } else {
                        $sql = "MATCH({$attribute->getAttribute()}) AGAINST(?)";
                        $attribute->isPartial()
                            ? $query->orWhereRaw($sql, [$searchTerm])
                            : $query->orWhere($attribute->getAttribute(), $searchTerm);    
                    }
                }
            }
        });
        foreach (Arr::wrap($attributes) as $attribute) {
            foreach ($searchTerms as $searchTerm) {
                $fieldSplit = explode('.', $attribute->getAttribute());
                $searchTerm = mb_strtolower($searchTerm, 'UTF8');
                if (count($fieldSplit) > 1) {
                    $query->orderByRaw("MATCH({$fieldSplit[1]}) AGAINST('$searchTerm') desc");   
                } 
                else {
                    $query->orderByRaw("MATCH({$attribute->getAttribute()}) AGAINST('$searchTerm') desc");    
                }
            }
        }        
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
            $query->groupby($this->groupby);
        }
    }

    public function addSearchScopes(Builder $query) {
        if (count($this->scopes) > 0) {
            foreach($this->scopes as $kscope => $vscope) {
                $query->$vscope();
            }
        }
    }


}
