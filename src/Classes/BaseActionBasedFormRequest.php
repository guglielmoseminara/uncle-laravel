<?php

namespace UncleProject\UncleLaravel\Classes;

use Illuminate\Foundation\Http\FormRequest;
use UncleProject\UncleLaravel\Classes\BaseRequestParser;
use RafflesArgentina\ActionBasedFormRequest\ActionBasedFormRequest;
use App;

class BaseActionBasedFormRequest extends FormRequest
{

    public static function destroyMany(){
        return [
            'ids'  => 'required|array',
        ];
    }

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    protected function validationData()
    {
        $data = $this->all();
        $newSearchData = [];
        if (isset($data['search'])) {
            $search = $data['search'];
            unset($data['search']);
            $searchData = BaseRequestParser::parserSearchData($search);
            foreach($searchData as $key => $value) {
                data_set($newSearchData, $key, $value);
            }    
        }
        return array_merge($newSearchData, $data);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];

        if (method_exists($this, 'sanitize')) {
            call_user_func([get_called_class(), 'sanitize']);
        }

        $action = explode('@', request()->route()->getActionName())[1];

        if (method_exists($this, $action)) {
            $rules = call_user_func([get_called_class(), $action]);
        }
        else{
            $xml = App::make('XMLResource');
            if($xml->hasXML()) {
                $method = $xml->getRequestMethod(class_basename(get_class($this)),$action);
                if($method){
                    $fields = $method->xpath('fields/field');
                    foreach ($fields as $field){
                        $rules_string = "";
                        foreach ($field->xpath('rule') as $rule){
                            $rules_string .= $rule->attributes()['name']->__toString();
                            if(isset($rule->attributes()['value'])) $rules_string .= ":".$rule->attributes()['value']->__toString();
                            $rules_string .= "|";
                        }

                        $rules[$field->attributes()['name']->__toString()] = rtrim($rules_string,'|');
                    }
                }
            }
        }

        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}