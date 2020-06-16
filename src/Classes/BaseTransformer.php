<?php

namespace UncleProject\UncleLaravel\Classes;

use League\Fractal\TransformerAbstract;
use Illuminate\Database\Eloquent\Model;
use App;

class BaseTransformer extends TransformerAbstract
{
    public function transform(Model $model)
    {
        $utils = App::make('Utils');

        $result = [];
        $xml = App::make('XMLResource');
        if($xml->hasXML()) {
            $xmlConfig = $xml->getTransformer(class_basename(get_class($this)));
            if($xmlConfig) {
                $fields = $xmlConfig->xpath('fields/field');
                if (!empty($fields)) {
                    foreach ($fields as $field) {
                        $value = $field->attributes()['value']->__toString();
                        $result[$field->attributes()['name']->__toString()] = $model->$value;
                    }
                }
            }
        }

        if(method_exists($this,'customReturns')) {
            $result = array_merge($result, $this->customReturns($model));
        }

        return $result;
    }

}