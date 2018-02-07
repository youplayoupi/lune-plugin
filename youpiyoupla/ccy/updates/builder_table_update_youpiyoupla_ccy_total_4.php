<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyTotal4 extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_total', function($table)
        {
            $table->dropColumn('exchanges_id');
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_total', function($table)
        {
            $table->integer('exchanges_id');
        });
    }
}
