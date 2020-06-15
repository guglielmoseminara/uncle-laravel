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
        if($migrations ) return $this->convertSingleToArray($migrations);
        else return null;
    }

    public function getRepository($name){
        $repository = $this->xml->xpath("//repository[@name='{$name}']");
        if($repository) return $this->convertSingleToArray($repository)[0];
        else return null;
    }

    public function getModel($name){
        $repository = $this->xml->xpath("//model[@name='{$name}']");
        if($repository) return $this->convertSingleToArray($repository)[0];
        else return null;
    }

    public function getRequestMethod($name, $action){
        $method = $this->xml->xpath("//request[@name='{$name}']/method[@name='{$action}']");
        if($method) return $this->convertSingleToArray($method)[0];
        else return null;
    }

    public function getResourceRoutes(){
        $routes = $this->xml->xpath("//resource/routes");
        if($routes) return $this->convertSingleToArray($routes);
        else return null;
    }

    public function convertSingleToArray($item){
        if(!is_array($item)) return array($item);
        else return $item;
    }
}
