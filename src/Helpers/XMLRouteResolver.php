<?php

namespace UncleProject\UncleLaravel\Helpers;

use App;

class XMLRouteResolver {

    public function createRoutes($api, $routes,$test = 0)
    {
        foreach ($routes as $key => $route){
            if($key == 'group'){
                $optionGroup = [];
                if(isset($route->attributes()['prefix'])) $optionGroup['prefix'] = $route->attributes()['prefix']->__toString();
                if(isset($route->attributes()['middleware'])) $optionGroup['middleware'] = explode('|',$route->attributes()['middleware']->__toString());
                $api->group($optionGroup, function ($api) use ($route){
                    $this->createRoutes($api, $route, 1);
                });
            }
            else{
                $this->generateRoute($api,$route->attributes());
            }

        }
    }

    private function generateRoute($api,$params){
        $method = $params['method']->__toString();
        if($method == 'match') {
            $acceptMethod = explode(',', $params['acceptMethod']->__toString());
            $api->$method($acceptMethod,$params['path'], App::make($params['resource']->__toString())->getControllerClassPath($params['controller']->__toString()) . '@'. $params['function']->__toString());
        }
        else $api->$method($params['path'], App::make($params['resource']->__toString())->getControllerClassPath($params['controller']->__toString()) . '@'. $params['function']->__toString());
    }


}
