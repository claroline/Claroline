<?php

namespace ICAP\LessonBundle\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20130619153000 extends BundleMigration
{
    /**
    * Will be fired at the plugin installation.
    * @param \Doctrine\DBAL\Schema\Schema $schema
    */
    public function up(Schema $schema)
    {
        $this->createLessonTable($schema);
        $this->createLessonChapterTable($schema);
    }

    /**
    * Will be fired at the plugin uninstallation.
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('icap__lesson');
        $schema->dropTable('icap__lesson_chapter');
    }

    /**
    * Create the 'claro_example_text' table.
    * @param \Doctrine\DBAL\Schema\Schema $schema
    */
    public function createLessonTable(Schema $schema)
    {
        // Table creation
        $table = $schema->createTable('icap__lesson');
        $this->addId($table);
        $this->storeTable($table);
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_resource'), array('id'), array('id'), array("onDelete" => "CASCADE")
        );
    }

    /**
     * Create the 'claro_example_text' table.
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function createLessonChapterTable(Schema $schema)
    {
        // Table creation
        $table = $schema->createTable('icap__lesson_chapter');
        // Add an auto increment id
        $this->addId($table);

        $table->addColumn('text', 'text', array('notnull' => false));
        $table->addColumn('title', 'string');
        $table->addColumn('lesson_id', 'integer');

        $this->storeTable($table);
        $table->addForeignKeyConstraint(
            $schema->getTable('icap__lesson'), array('lesson_id'), array('id'), array("onDelete" => "CASCADE")
        );
    }
}