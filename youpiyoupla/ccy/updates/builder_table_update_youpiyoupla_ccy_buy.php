<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyBuy extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_buy', function($table)
        {
            $table->decimal('rate_avg', 40, 30)->nullable()->change();
            $table->decimal('amount_usd', 20, 2)->nullable()->change();
            $table->decimal('total', 40, 30)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_buy', function($table)
        {
            $table->decimal('rate_avg', 40, 30)->nullable(false)->change();
            $table->decimal('amount_usd', 20, 2)->nullable(false)->change();
            $table->decimal('total', 40, 30)->nullable(false)->change();
        });
    }
}
