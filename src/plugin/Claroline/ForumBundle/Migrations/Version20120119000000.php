<?php

namespace Claroline\ForumBundle\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120119000000 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createForumTable($schema);
        $this->createForumSubjectTable($schema);
        $this->createForumMessageTable($schema);
        $this->createForumParametersTable($schema);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('claro_forum_message');
        $schema->dropTable('claro_forum_subject');
        $schema->dropTable('claro_forum');
    }

    private function createForumTable(Schema $schema)
    {
        $table = $schema->createTable('claro_forum');

        $this->addId($table);
        $this->storeTable($table);
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_resource'), array('id'), array('id'), array("onDelete" => "CASCADE")
        );
    }

    private function createForumSubjectTable(Schema $schema)
    {
        $table = $schema->createTable('claro_forum_subject');

        $this->addId($table);
        $table->addColumn('title', 'string', array('length' => 250));
        $table->addColumn('forum_id', 'integer');
        $this->storeTable($table);
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_resource'), array('id'), array('id'), array("onDelete" => "CASCADE")
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_forum'), array('forum_id'), array('id'), array("onDelete" => "CASCADE")
        );
    }

    private function createForumMessageTable(Schema $schema)
    {
        $table = $schema->createTable('claro_forum_message');

        $this->addId($table);
        $table->addColumn('content', 'text');
        $table->addColumn('forum_subject_id', 'integer');
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_resource'), array('id'), array('id'), array("onDelete" => "CASCADE")
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_forum_subject'), array('forum_subject_id'), array('id'), array("onDelete" => "CASCADE")
        );
    }
/*
    private function createForumParametersTable(Schema $schema)
    {
        $table = $schema->createTable('claro_forum_parameters');
        $this->addId($table);
        $table->addColumn('')
    }*/
}