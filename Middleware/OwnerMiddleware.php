<?php

namespace UncleProject\UncleLaravel\Middleware;

use Closure;
use App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class OwnerMiddleware {

    public function handle($request, Closure $next, $resource, $model, $key, $identifier = 'id') {
        if (Auth::guest()) {
            throw new AccessDeniedHttpException();
        }
        $modelInstance = App::make($resource.'Resource')->getModelInstance($model);
        $row = $modelInstance->find($request->$identifier);
        $user = Auth::user();
        $roles = $user->getRoleNames()->toArray();
        if (!isset($row->$key) || (isset($row->$key) && $row->$key != $user->id && !in_array('admin', $roles))) {
            throw new AccessDeniedHttpException('Resource is not owned by user');
        }

        return $next($request);
    }
}
