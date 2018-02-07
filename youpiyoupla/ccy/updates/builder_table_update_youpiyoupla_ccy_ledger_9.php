<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyLedger9 extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_ledger', function($table)
        {
            $table->decimal('amount', 20, 10)->change();
            $table->decimal('rate', 20, 10)->change();
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_ledger', function($table)
        {
            $table->decimal('amount', 10, 10)->change();
            $table->decimal('rate', 10, 10)->change();
        });
    }
}
