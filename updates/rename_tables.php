<?php namespace OctoDevel\OctoMail\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class RenameTables extends Migration
{

    public function up()
    {
        if ( Schema::hasTable('octodevel_octomail_templates') )
        {
            Schema::rename('octodevel_octomail_templates', 'od_octomail_templates');
        }

        if ( Schema::hasTable('octodevel_octomail_logs') )
        {
            Schema::rename('octodevel_octomail_logs', 'od_octomail_logs');
        }

        if ( Schema::hasTable('octodevel_octomail_tem_rec') )
        {
            Schema::rename('octodevel_octomail_tem_rec', 'od_octomail_template_records');
        }

        if ( Schema::hasTable('octodevel_octomail_templates_recipients') )
        {
            Schema::rename('octodevel_octomail_templates_recipients', 'od_octomail_templates_recipients');
        }
    }

    public function down()
    {
        return true;
    }
}
