<?php

namespace UncleProject\UncleLaravel\Helpers;


class XMLResource {

    private $xml;

    public function __construct($app ,$resource) {
        $this->load($resource);
    }

    public function load($resource){
        $filepath = app_path('Http'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.$resource.DIRECTORY_SEPARATOR.$resource).'.xml';
        if(\File::exists($filepath))
            $this->xml = simplexml_load_file($filepath);
    }

    public function hasXML(){
        return isset($this->xml);
    }

    public function getDatabaseSchemas(){
        $migrations = $this->xml->xpath('migrations/schema');
        return $this->convertSingleToArray($migrations);
    }

    public function getRepositorySearchable($name){
        $migrations = $this->xml->xpath("//repository[@name='{$name}']/searchables/field");
        return $this->convertSingleToArray($migrations);
    }

    public function convertSingleToArray($item){
        if(!is_array($item)) return array($item);
        else return $item;
    }
}
