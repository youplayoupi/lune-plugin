<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyLedger8 extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_ledger', function($table)
        {
            $table->decimal('amount', 10, 10)->nullable(false)->unsigned(false)->default(null)->change();
            $table->decimal('rate', 10, 10)->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_ledger', function($table)
        {
            $table->integer('amount')->nullable(false)->unsigned(false)->default(null)->change();
            $table->integer('rate')->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
}
