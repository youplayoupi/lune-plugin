<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyLedger10 extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_ledger', function($table)
        {
            $table->dateTime('date');
            $table->boolean('buyorsell');
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_ledger', function($table)
        {
            $table->dropColumn('date');
            $table->dropColumn('buyorsell');
        });
    }
}
