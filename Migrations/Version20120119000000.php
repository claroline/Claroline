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
        $this->createForumOptionsTable($schema);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('claro_forum_message');
        $schema->dropTable('claro_forum_subject');
        $schema->dropTable('claro_forum');
        $schema->dropTable('claro_forum_options');
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
        $this->storeTable($table);
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_resource'), array('id'), array('id'), array("onDelete" => "CASCADE")
        );
    }

    private function createForumMessageTable(Schema $schema)
    {
        $table = $schema->createTable('claro_forum_message');

        $this->addId($table);
        $table->addColumn('content', 'text');
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_resource'), array('id'), array('id'), array("onDelete" => "CASCADE")
        );
    }

    private function createForumOptionsTable(Schema $schema)
    {
        $table = $schema->createTable('claro_forum_options');

        $this->addId($table);
        $table->addColumn('subjects', 'integer');
        $table->addColumn('messages', 'integer');
    }
}