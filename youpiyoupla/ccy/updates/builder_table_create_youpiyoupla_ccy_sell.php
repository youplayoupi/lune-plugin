<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateYoupiyouplaCcySell extends Migration
{
    public function up()
    {
        Schema::create('youpiyoupla_ccy_sell', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('coins_id');
            $table->integer('user_id');
            $table->decimal('rate_avg', 40, 30);
            $table->decimal('amount_usd', 20, 2);
            $table->decimal('total', 40, 30);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('youpiyoupla_ccy_sell');
    }
}
