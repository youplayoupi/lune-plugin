<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyApis3 extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_apis', function($table)
        {
            $table->boolean('active')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_apis', function($table)
        {
            $table->dropColumn('active');
        });
    }
}
