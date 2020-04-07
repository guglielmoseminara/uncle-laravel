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
        $xml = App::make('XMLResource',['resource' => $this->resourceName]);
        $migrations = $xml->getDatabaseSchemas();

        foreach($migrations as $schema){
            Schema::create($schema->attributes()['name'], function (Blueprint $table) use($schema) {
                foreach ($schema->xpath('column') as $column){
                    $type = $column->attributes()['type']->__toString();
                    $table->$type($column->attributes()['name']);
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
        Schema::dropIfExists('tests');
    }
}