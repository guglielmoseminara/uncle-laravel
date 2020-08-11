<?php

namespace UncleProject\UncleLaravel\Criterias;

use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Contracts\CriteriaInterface;

class EqualCriteria implements CriteriaInterface {

    public function __construct($field, $value) {
        $this->field = $field;
        $this->value = $value;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        $model = $model->where($this->field,'=', $this->value);
        return $model;
    }
}
