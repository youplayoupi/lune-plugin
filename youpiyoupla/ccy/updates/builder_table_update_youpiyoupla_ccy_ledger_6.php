<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyLedger6 extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_ledger', function($table)
        {
            $table->renameColumn('coin_id', 'coins_id');
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_ledger', function($table)
        {
            $table->renameColumn('coins_id', 'coin_id');
        });
    }
}
