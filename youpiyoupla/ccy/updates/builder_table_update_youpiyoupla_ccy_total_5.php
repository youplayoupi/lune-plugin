<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyTotal5 extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_total', function($table)
        {
            $table->decimal('total', 40, 30)->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_total', function($table)
        {
            $table->integer('total')->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
}
