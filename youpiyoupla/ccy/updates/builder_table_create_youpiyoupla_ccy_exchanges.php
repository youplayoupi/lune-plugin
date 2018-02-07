<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateYoupiyouplaCcyExchanges extends Migration
{
    public function up()
    {
        Schema::create('youpiyoupla_ccy_exchanges', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('name');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('youpiyoupla_ccy_exchanges');
    }
}
