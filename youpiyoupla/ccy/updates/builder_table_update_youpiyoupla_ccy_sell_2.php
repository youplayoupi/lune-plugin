<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcySell2 extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_sell', function($table)
        {
            $table->increments('id')->unsigned(false)->change();
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_sell', function($table)
        {
            $table->increments('id')->unsigned()->change();
        });
    }
}
