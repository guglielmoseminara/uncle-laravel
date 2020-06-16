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

    public function getRepository($name){
        return $this->getXMLElement("//repository[@name='{$name}']");
    }

    public function getModel($name){
        return $this->getXMLElement("//model[@name='{$name}']");
    }

    public function getTransformer($name){
        return $this->getXMLElement("//transformer[@name='{$name}']");
    }

    public function getRequestMethod($name, $action){
        return $this->getXMLElement("//request[@name='{$name}']/method[@name='{$action}']");
    }

    public function getResourceRoutes(){
        return $this->getXMLElements("//resource/routes");
    }

    public function getResourceDatabaseSchemas($name){
        return $this->getXMLElements("//resource[@name='{$name}']/migrations/schema");
    }

    public function getXMLElement($xpath){
        $element = $this->xml->xpath($xpath);
        if($element) return $this->convertSingleToArray($element)[0];
        else return null;
    }

    public function getXMLElements($xpath){
        $element = $this->xml->xpath($xpath);
        if($element) return $this->convertSingleToArray($element);
        else return null;
    }

    public function convertSingleToArray($item){
        if(!is_array($item)) return array($item);
        else return $item;
    }
}