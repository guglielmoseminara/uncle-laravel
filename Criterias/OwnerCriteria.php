<?php

namespace UncleProject\UncleLaravel\Criterias;

use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Contracts\CriteriaInterface;
use Auth;

class OwnerCriteria implements CriteriaInterface {

    private $user_id;

    public function __construct($user_id = null) {
        if($user_id) $this->user_id = $user_id;
        else{
            $user = Auth::user();
            $this->user_id = $user->id;
        }

    }

    public function apply($model, RepositoryInterface $repository)
    {
        $model = $model->where('user_id', '=', $this->user_id);
        return $model;
    }
}
