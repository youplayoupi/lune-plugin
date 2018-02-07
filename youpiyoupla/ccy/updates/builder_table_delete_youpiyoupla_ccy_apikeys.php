<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteYoupiyouplaCcyApikeys extends Migration
{
    public function up()
    {
        Schema::dropIfExists('youpiyoupla_ccy_apikeys');
    }
    
    public function down()
    {
        Schema::create('youpiyoupla_ccy_apikeys', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id');
            $table->integer('exchanges_id');
            $table->string('public', 255);
            $table->string('private', 255);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
}
