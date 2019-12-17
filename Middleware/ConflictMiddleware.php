<?php

namespace UncleProject\UncleLaravel\Middleware;

use Closure;
use App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ConflictMiddleware {

    public function handle($request, Closure $next, $resource, $model, $conflictKeys) {

        $modelInstance = App::make($resource.'Resource')->getModelInstance($model);

        $conflictKeys = explode(';', $conflictKeys);
        foreach($conflictKeys as $key) {
            if($key == 'user_id'){
                $modelInstance = $modelInstance->where($key, Auth::User()->id);
            }
            else $modelInstance = $modelInstance->where($key, $request->$key);
        }
        $row = $modelInstance->first();

        //dd($row);
        if ($row) {
            throw new ConflictHttpException('Element conflict to insert');
        }

        return $next($request);
    }
}
