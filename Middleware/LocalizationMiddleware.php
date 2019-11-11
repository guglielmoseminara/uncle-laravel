<?php

namespace UncleProject\UncleLaravel\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App;

class LocalizationMiddleware {

    public function handle($request, Closure $next) {
        $locale = $request->header('x-locale');
        if(!$locale){
            $locale = config('app.locale');
        }
        if (!in_array($locale, config('app.locales'))) {
            throw new AccessDeniedHttpException();
        }
        App::setLocale($locale);
        $response = $next($request);
        $response->headers->set('x-locale', $locale);
        return $response;
    }

}
