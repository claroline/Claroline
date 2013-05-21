<?php

namespace Claroline\ScormBundle\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20130429000000 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createScormTable($schema);
        $this->createScormInfoTable($schema);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('claro_scorm');
        $schema->dropTable('claro_scorm_info');
    }

    public function createScormTable(Schema $schema)
    {
        $table = $schema->createTable('claro_scorm');
        $this->addId($table);
        $table->addColumn('hash_name', 'string', array('length' => 50));
        $table->addColumn('launch_data', 'string', array('length' => 255, 'notnull' => false));
        $table->addColumn('mastery_score', 'integer', array('notnull' => false));
        $table->addColumn('entry_url', 'string', array('length' => 255, 'notnull' => true));
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_resource'), array('id'), array('id'), array("onDelete" => "CASCADE")
        );
        $this->storeTable($table);
    }

    public function createScormInfoTable(Schema $schema)
    {
        $table = $schema->createTable('claro_scorm_info');
        $this->addId($table);
        $table->addColumn('user_id', 'integer', array('notnull' => true));
        $table->addColumn('scorm_id', 'integer', array('notnull' => true));
        $table->addColumn('score_raw', 'integer', array('notnull' => false));
        $table->addColumn('score_min', 'integer', array('notnull' => false));
        $table->addColumn('score_max', 'integer', array('notnull' => false));
        $table->addColumn('session_time', 'integer', array('notnull' => false));
        $table->addColumn('total_time', 'integer', array('notnull' => false));
        $table->addColumn('lesson_status', 'string', array('length' => 255, 'notnull' => false));
        $table->addColumn('entry', 'string', array('length' => 255, 'notnull' => false));
        $table->addColumn('suspend_data', 'string', array('length' => 255, 'notnull' => false));
        $table->addColumn('credit', 'string', array('length' => 255, 'notnull' => false));
        $table->addColumn('exit_mode', 'string', array('length' => 255, 'notnull' => false));
        $table->addColumn('lesson_location', 'string', array('length' => 255, 'notnull' => false));
        $table->addColumn('lesson_mode', 'string', array('length' => 255, 'notnull' => false));

        $table->addForeignKeyConstraint(
            $schema->getTable('claro_user'), array('user_id'), array('id'), array("onDelete" => "CASCADE")
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_scorm'), array('scorm_id'), array('id'), array("onDelete" => "CASCADE")
        );

        $table->addUniqueIndex(array('user_id', 'scorm_id'));
    }
}