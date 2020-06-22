<?php

namespace UncleProject\UncleLaravel\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

trait LanguageRequest {

    public function rules(){
        if (method_exists($this, 'sanitize')) {
            call_user_func([get_called_class(), 'sanitize']);
        }
        $action = explode('@', request()->route()->getActionName())[1];

        if (method_exists($this, $action)) {
            $rules = $this->generateRules(call_user_func([get_called_class(), $action]));
            return $rules;
        }

        return [];
    }

    protected function generateRules($rules) {
        $languageFields = Arr::where(
            $rules, function ($value, $key) {
                if (is_array($value)) {
                    $value = implode('|', $value);
                }
                return Str::contains($value, ['language']);
            }
        );
        $locales = config('app.locales');   
        foreach($languageFields as $field => $field_rules) {
            $withoutAll = [];
            $field_rules = str_replace('language|', '', $field_rules);
            foreach($locales as $lang) {
                $withoutAll[] = '"'.$field.':'.$lang.'"';
                $fields_rules_lang = $field_rules;
                if (Str::contains($fields_rules_lang, ['required'])){
                    $fields_rules_lang .= '|sometimes';
                }
                $rules[$field.':'.$lang] = $fields_rules_lang;

            }
            if (Str::contains($field_rules, ['required'])){
                $field_rules = str_replace('required|', '', $field_rules);
                $rules[$field] = $field_rules.'|required_without_all:'.implode(',', $withoutAll);
            } else {
                $rules[$field] = $field_rules;
            }
        }
        return $rules;
    }

}