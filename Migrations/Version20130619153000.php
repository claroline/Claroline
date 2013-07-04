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
        $table->addColumn('root_id', 'integer', array('notnull' => false));
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
        $table->addColumn('title', 'string', array('notnull' => false));
        $table->addColumn('lesson_id', 'integer');
        $table->addColumn('left', 'integer');
        $table->addColumn('level', 'integer');
        $table->addColumn('right', 'integer');
        $table->addColumn('root', 'integer', array('notnull' => false));
        $table->addColumn('parent_id', 'integer', array('notnull' => false));


        $this->storeTable($table);
        $table->addForeignKeyConstraint(
            $schema->getTable('icap__lesson'), array('lesson_id'), array('id'), array("onDelete" => "CASCADE")
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('icap__lesson_chapter'), array('parent_id'), array('id'), array("onDelete" => "CASCADE")
        );

        $schema->getTable('icap__lesson')->addForeignKeyConstraint(
            $table, array('root_id'), array('id'), array("onDelete" => "CASCADE")
        );

    }
}