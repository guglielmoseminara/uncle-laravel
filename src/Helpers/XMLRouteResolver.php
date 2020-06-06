<?php

namespace UncleProject\UncleLaravel\Helpers;

use App;

class XMLRouteResolver {

    public function createRoutes($api, $routes)
    {
        foreach ($routes as $key => $route){
            if($key == 'group'){
                $optionGroup = [];
                if(isset($route->attributes()['prefix'])) $optionGroup['prefix'] = $route->attributes()['prefix']->__toString();
                if(isset($route->attributes()['middleware'])) $optionGroup['middleware'] = explode('|',$route->attributes()['middleware']->__toString());
                $subroute = $route->xpath('routes')[0];
                $api->group($optionGroup, function ($api) use ($subroute){
                    $this->createRoutes($api, $subroute);
                });
            }
            else{
                $this->generateRoute($api,$route->attributes());
            }

        }
    }

    private function generateRoute($api,$params){
        $method = $params['method']->__toString();
        if($method == 'match')
        {
            $api->$method($params['path'], App::make($params['resource']->__toString())->getControllerClassPath($params['controller']->__toString()) . '@'. $params['function']->__toString());
        }
        else $api->$method($params['path'], App::make($params['resource']->__toString())->getControllerClassPath($params['controller']->__toString()) . '@'. $params['function']->__toString());
    }


}
