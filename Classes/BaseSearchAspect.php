<?php

namespace UncleProject\UncleLaravel\Classes;

use Spatie\Searchable\Exceptions\InvalidModelSearchAspect;
use Spatie\Searchable\ModelSearchAspect;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Arr;
use App;
use Schema;

class BaseSearchAspect extends ModelSearchAspect {

    protected $conditions = [];

    public function __construct($model, $attributes) {
        $newAttributes = [];
        $conditions = [];
        foreach ($attributes as $key => $attribute) {
            if (!is_array($attribute)) {
                $newAttributes[] = $attribute;
            } else {
                foreach ($attribute as $key => $value) {
                    $conditions[$key] = $value;
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

        return $query->take(10)->get();
    }

    public function addSearchConditions(Builder $query, string $term)
    {
        $attributes = $this->attributes;
        $searchTerms = [$term];

        $query->where(function (Builder $query) use ($attributes, $term, $searchTerms) {
            foreach (Arr::wrap($attributes) as $attribute) {
                foreach ($searchTerms as $searchTerm) {
                    $sql = "LOWER({$attribute->getAttribute()}) LIKE ?";
                    $searchTerm = mb_strtolower($searchTerm, 'UTF8');
                    $attribute->isPartial()
                        ? $query->orWhereRaw($sql, ["%{$searchTerm}%"])
                        : $query->orWhere($attribute->getAttribute(), $searchTerm);
                }
            }
        });
        if (count($this->conditions) > 0) {
            foreach ($this->conditions as $key => $value) {
                if (Schema::hasColumn(App::make($this->model)->getTable(), $key)) {
                    $query->where($key, '=', $value);
                }
            }
        }
    }

}
