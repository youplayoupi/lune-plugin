<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateYoupiyouplaCcyCoins extends Migration
{
    public function up()
    {
        Schema::create('youpiyoupla_ccy_coins', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('icon');
            $table->string('name');
            $table->string('abv');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('youpiyoupla_ccy_coins');
    }
}
