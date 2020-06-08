<?php

namespace UncleProject\UncleLaravel\XMLCore;

use UncleProject\UncleLaravel\Helpers\XMLRouteResolver;
use Dingo\Api\Routing\Router;
use App;

/*$api = app(Router::class);
$xml = App::make('XMLResource',['resource' => 'Tests']);
$routes = $xml->getResourceRoutes('Tests');
$routeRevolver = new XMLRouteResolver();

$api->version('v1', function ($api) use ($routes, $routeRevolver){

    foreach ($routes as $route)
        $routeRevolver->createRoutes($api, $route);

});*/
