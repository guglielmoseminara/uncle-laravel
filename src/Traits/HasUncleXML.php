<?php

namespace UncleProject\UncleLaravel\Traits;

use Illuminate\Database\Eloquent\Builder;
use App;

trait HasUncleXML {

    protected $xml_relations = [];

    function initializeHasUncleXML(){

        $xml = App::make('XMLResource');
        if($xml->hasXML()) {
            $xmlConfig = $xml->getModel(class_basename(get_class($this)));
            if($xmlConfig) {
                $fillables = $xmlConfig->xpath('fillables/field');
                if (!empty($fillables)) {
                    foreach ($fillables as $fillable) {
                        array_push($this->fillable, $fillable->attributes()['name']->__toString());
                    }
                }

                $relations = $xmlConfig->xpath('relations/relation');
                if (!empty($relations)) {
                    foreach ($relations as $relation) {
                        $relation_name = $relation->attributes()['name']->__toString();
                        $this->xml_relations[$relation_name] = current($relation->attributes());
                    }

                }
            }
        }
    }

    public function __call($method, $parameters){
        if(array_key_exists($method, $this->xml_relations)){
            $relation = $this->xml_relations[$method];
            switch($relation['type']){
                case 'hasOne':
                    $foreignKey = (isset($relation['foreignKey']))? $relation['foreignKey'] : null;
                    $localKey   = (isset($relation['localKey']))? $relation['localKey'] : null;
                    return $this->hasOne(App::make($relation['resource'])->getModelClassPath($relation['model']), $foreignKey, $localKey);
                    break;

                case 'hasMany':
                    $foreignKey = (isset($relation['foreignKey']))? $relation['foreignKey'] : null;
                    $localKey   = (isset($relation['localKey']))? $relation['localKey'] : null;
                    return $this->hasMany(App::make($relation['resource'])->getModelClassPath($relation['model']), $foreignKey, $localKey);
                    break;

                case 'belongsTo':
                    $foreignKey  = (isset($relation['foreignKey']))? $relation['foreignKey'] : null;
                    $ownerKey    = (isset($relation['ownerKey']))? $relation['ownerKey'] : null;
                    return $this->belongsTo(App::make($relation['resource'])->getModelClassPath($relation['model']), $foreignKey, $ownerKey, $method);
                    break;
            }

        }

        return parent::__call($method, $parameters); // TODO: Change the autogenerated stub
    }
}