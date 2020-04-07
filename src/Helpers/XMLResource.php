<?php

namespace UncleProject\UncleLaravel\Helpers;


class XMLResource {

    private $xml;

    public function __construct($app ,$resource) {
        $this->load($resource);
    }

    public function load($resource){
        $this->xml = simplexml_load_file(app_path('Http'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.$resource.DIRECTORY_SEPARATOR.$resource).'.xml');
    }


    public function getDatabaseSchemas(){
        $migrations = $this->xml->xpath('migrations/schema');
        return $this->convertSingleToArray($migrations);
    }

    public function convertSingleToArray($item){
        if(!is_array($item)) return array($item);
        else return $item;
    }
}
