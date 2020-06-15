<?php

namespace UncleProject\UncleLaravel\Classes;

use Illuminate\Database\Eloquent\Model;
use App;

class BaseModel extends Model
{
    public static function boot() {
        $xml = App::make('XMLResource');
        if($xml->hasXML()) {
            $xmlConfig = $xml->getModel(class_basename(get_class()));
            if($xmlConfig) {
                $fillables = $xmlConfig->xpath('fillables/field');
                if (!empty($fillables)) {
                    foreach ($fillables as $fillable) {
                        array_push(self::$fillable, $fillable->attributes()['name']->__toString());
                    }
                }
            }
        }
        parent::boot();

    }
}