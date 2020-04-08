<?php

namespace UncleProject\UncleLaravel\Criterias;

use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Contracts\CriteriaInterface;

class WithTrashedCriteria implements CriteriaInterface {
    public function __construct() {

    }

    public function apply($model, RepositoryInterface $repository)
    {
        $model = $model->withTrashed();
        return $model;
    }
}
