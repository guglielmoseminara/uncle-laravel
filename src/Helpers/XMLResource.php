<?php

namespace UncleProject\UncleLaravel\Helpers;


class XMLResource {

    private $xml;

    public function __construct($app) {
        $this->load();
    }

    public function load($resource = null){
        if(isset($resource)) $xmlPath = app_path('Http'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.$resource.DIRECTORY_SEPARATOR.$resource).'.xml';
        else $xmlPath = app_path('Http'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'api.uncle.xml');
        if(\File::exists($xmlPath))
            $this->xml = simplexml_load_file($xmlPath);
    }

    public function hasXML(){
        return isset($this->xml);
    }

    public function getResourceDatabaseSchemas($name){
        $migrations = $this->xml->xpath("//resource[@name='{$name}']/migrations/schema");
        return $this->convertSingleToArray($migrations);
    }

    public function getRepository($name){
        $migrations = $this->xml->xpath("//repository[@name='{$name}']");
        return $this->convertSingleToArray($migrations);
    }

    public function getResourceRoutes(){
        $routes = $this->xml->xpath("//resource/routes");
        return $this->convertSingleToArray($routes);
    }

    public function convertSingleToArray($item){
        if(!is_array($item)) return array($item);
        else return $item;
    }
}
