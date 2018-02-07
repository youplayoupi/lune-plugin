<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateYoupiyouplaCcyLedger extends Migration
{
    public function up()
    {
        Schema::create('youpiyoupla_ccy_ledger', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('coin_id');
            $table->integer('amount');
            $table->integer('exchange_id');
            $table->integer('rate');
            $table->integer('currency_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('youpiyoupla_ccy_ledger');
    }
}
