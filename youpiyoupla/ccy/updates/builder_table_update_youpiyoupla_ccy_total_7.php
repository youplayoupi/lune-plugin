<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyTotal7 extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_total', function($table)
        {
            $table->decimal('amount_usd', 20, 2)->nullable()->change();
            $table->decimal('rate_avg', 40, 30)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_total', function($table)
        {
            $table->decimal('amount_usd', 20, 2)->nullable(false)->change();
            $table->decimal('rate_avg', 40, 30)->nullable(false)->change();
        });
    }
}
