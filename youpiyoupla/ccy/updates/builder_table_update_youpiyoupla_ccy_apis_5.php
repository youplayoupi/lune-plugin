<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyApis5 extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_apis', function($table)
        {
            $table->text('privatekey')->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_apis', function($table)
        {
            $table->string('privatekey', 255)->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
}
