<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyLedger12 extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_ledger', function($table)
        {
            $table->decimal('amount', 40, 30)->change();
            $table->decimal('rate', 40, 30)->change();
            $table->decimal('total', 40, 30)->change();
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_ledger', function($table)
        {
            $table->decimal('amount', 20, 10)->change();
            $table->decimal('rate', 20, 10)->change();
            $table->decimal('total', 20, 10)->change();
        });
    }
}
