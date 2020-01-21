<?php

namespace UncleProject\UncleLaravel\Classes;

use Illuminate\Foundation\Http\FormRequest;
use UncleProject\UncleLaravel\Classes\BaseRequestParser;
use RafflesArgentina\ActionBasedFormRequest\ActionBasedFormRequest;

class BaseActionBasedFormRequest extends ActionBasedFormRequest
{
    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public static function destroyMany(){
        return [
            'ids'  => 'required|array',
        ];
    }

    protected function validationData()
    {
        $data = $this->all();
        $newSearchData = [];
        if (isset($data['search'])) {
            $search = $data['search'];
            unset($data['search']);
            $searchData = BaseRequestParser::parserSearchData($search);
            foreach($searchData as $key => $value) {
                array_set($newSearchData, $key, $value);
            }    
        }
        return array_merge($newSearchData, $data);
    }

}