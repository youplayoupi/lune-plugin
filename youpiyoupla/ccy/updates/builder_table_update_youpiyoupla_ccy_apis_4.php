<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyApis4 extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_apis', function($table)
        {
            $table->string('passphrase')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_apis', function($table)
        {
            $table->dropColumn('passphrase');
        });
    }
}
