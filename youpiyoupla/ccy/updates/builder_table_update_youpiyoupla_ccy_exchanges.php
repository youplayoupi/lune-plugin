<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyExchanges extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_exchanges', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->increments('id')->unsigned(false)->change();
        });

		// Insert some stuff
		DB::table('youpiyoupla_ccy_exchanges')->insert(
			array(
				'id' => 1,
				'name' => 'Binance'
			)
		);
		DB::table('youpiyoupla_ccy_exchanges')->insert(
			array(
				'id' => 2,
				'name' => 'Cryptopia 	'
			)
		);
		DB::table('youpiyoupla_ccy_exchanges')->insert(
			array(
				'id' => 3,
				'name' => 'GDAX'
			)
		);
		DB::table('youpiyoupla_ccy_exchanges')->insert(
			array(
				'id' => 4,
				'name' => 'Coinbase'
			)
		);
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_exchanges', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
            $table->increments('id')->unsigned()->change();
        });
    }
}
