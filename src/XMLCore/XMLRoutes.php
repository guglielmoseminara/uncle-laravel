<?php

namespace UncleProject\UncleLaravel\XMLCore;

use UncleProject\UncleLaravel\Helpers\XMLRouteResolver;
use Dingo\Api\Routing\Router;
use App;

$api = app(Router::class);
$xml = App::make('XMLResource');

if($xml->hasXML()){
    $routes = $xml->getResourceRoutes();
    $routeRevolver = new XMLRouteResolver();

    if($routes) {
        foreach ($routes as $resourceRoutes) {
            foreach ($resourceRoutes as $versionRoutes) {
                $api->version($versionRoutes->attributes()['v']->__toString(), function ($api) use ($versionRoutes, $routeRevolver) {
                    $routeRevolver->createRoutes($api, $versionRoutes);
                });
            }
        }
    }
}

