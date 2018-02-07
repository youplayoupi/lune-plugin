<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateYoupiyouplaCcyTotal extends Migration
{
    public function up()
    {
        Schema::create('youpiyoupla_ccy_total', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('coin_id');
            $table->integer('total');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('youpiyoupla_ccy_total');
    }
}
