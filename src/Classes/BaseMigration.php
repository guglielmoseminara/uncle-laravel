<?php

namespace UncleProject\UncleLaravel\Classes;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App;

class BaseMigration extends Migration
{
    protected $resourceName;
    /**
     * Run the migrations.
     *
     * @return void
     */


    public function up()
    {
        $xml = App::make('XMLResource');
        $migrations = $xml->getResourceDatabaseSchemas($this->resourceName);

        foreach($migrations as $schema){
            Schema::create($schema->attributes()['name'], function (Blueprint $table) use($schema) {
                foreach ($schema->xpath('column') as $column){
                    $type = $column->attributes()['type']->__toString();

                    if($type == 'foreign'){
                        $field = $table->$type($column->attributes()['name'])->references($column->attributes()['references'])->on($column->attributes()['on']);
                    }
                    elseif(in_array($type,['char','string']) && isset($column->attributes()['length'])){
                        $field = $table->$type($column->attributes()['name'], $column->attributes()['length']);
                    }
                    elseif(in_array($type,['double','decimal','float','unsignedDecimal']) ){
                        $field  = $table->$type($column->attributes()['name'], $column->attributes()['digits'],$column->attributes()['decimal']);
                    }
                    elseif(in_array($type,['enum','set']) ){
                        $field = $table->$type($column->attributes()['name'], explode(',',$column->attributes()['options']->__toString()));
                    }
                    else $field = $table->$type($column->attributes()['name']);

                    if(isset($column->attributes()['modifiers'])){
                        $modifiers = explode(',',$column->attributes()['modifiers']->__toString());
                        foreach ($modifiers as $modifier){
                            [$key, $value] = explode(':',$modifier);
                            if(isset($value)) $field->$key($value);
                            else $field->$modifier();
                        }
                    }
                }
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $xml = App::make('XMLResource',['resource' => $this->resourceName]);
        $migrations = $xml->getDatabaseSchemas();

        foreach($migrations as $schema){
            Schema::dropIfExists($schema->attributes()['name']);
        }

    }
}