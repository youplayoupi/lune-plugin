<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateYoupiyouplaCcyApikeys extends Migration
{
    public function up()
    {
        Schema::create('youpiyoupla_ccy_apikeys', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('exchanges_id');
            $table->string('public');
            $table->string('private');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('youpiyoupla_ccy_apikeys');
    }
}
