<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyApis extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_apis', function($table)
        {
            $table->renameColumn('secret', 'public');
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_apis', function($table)
        {
            $table->renameColumn('public', 'secret');
        });
    }
}
