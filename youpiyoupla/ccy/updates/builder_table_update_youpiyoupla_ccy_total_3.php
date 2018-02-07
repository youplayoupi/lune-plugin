<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyTotal3 extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_total', function($table)
        {
            $table->integer('coins_id');
            $table->integer('exchanges_id');
            $table->dropColumn('coin_id');
            $table->dropColumn('exchange_id');
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_total', function($table)
        {
            $table->dropColumn('coins_id');
            $table->dropColumn('exchanges_id');
            $table->integer('coin_id');
            $table->integer('exchange_id');
        });
    }
}
