<?php

namespace UncleProject\UncleLaravel\Middleware;

use Closure;
use App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BelongsMiddleware {

    public function handle($request, Closure $next, $resource, $model, $key, $identifier = 'id') {
        if (Auth::guest()) {
            throw new AccessDeniedHttpException();
        }
        $modelInstance = App::make($resource.'Resource')->getModelInstance($model);
        $row = $modelInstance->find($request->$identifier);
        if (!isset($row->$key) || (isset($row->$key) && $row->$key != $request->$key)) {
            throw new AccessDeniedHttpException('Child element is not belongs to Parent element');
        }

        return $next($request);
    }
}
