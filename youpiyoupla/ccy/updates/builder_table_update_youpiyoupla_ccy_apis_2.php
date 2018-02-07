<?php namespace Youpiyoupla\Ccy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateYoupiyouplaCcyApis2 extends Migration
{
    public function up()
    {
        Schema::table('youpiyoupla_ccy_apis', function($table)
        {
            $table->string('privatekey', 255);
            $table->string('publickey', 255);
            $table->dropColumn('private');
            $table->dropColumn('public');
        });
    }
    
    public function down()
    {
        Schema::table('youpiyoupla_ccy_apis', function($table)
        {
            $table->dropColumn('privatekey');
            $table->dropColumn('publickey');
            $table->string('private', 255);
            $table->string('public', 255);
        });
    }
}
