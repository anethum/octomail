<?php namespace OctoDevel\OctoMail\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddSpamPrevention extends Migration
{

    public function up()
    {
        if ( Schema::hasTable('octodevel_octomail_templates') and !Schema::hasColumn('octodevel_octomail_templates', 'spam') )
        {
            Schema::table('octodevel_octomail_templates', function($table)
            {
                $table->integer('spam')->default(1);
            });
        }
    }

    public function down()
    {
        return true;
    }
}
