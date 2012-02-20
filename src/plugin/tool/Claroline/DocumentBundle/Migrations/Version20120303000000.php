<?php

namespace Claroline\DocumentBundle\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120303000000 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createDirectoryTable($schema);
        $this->createDocumentTable($schema);

        //$this->createDirectoryDocumentTable($schema);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('claro_document');
        $schema->dropTable('claro_directory');
        // $schema->dropTable('claro_directory_document');
    }

    private function createDocumentTable(Schema $schema)
    {
        $table = $schema->createTable('claro_document');

        $this->addId($table);
        $table->addColumn('name', 'string', array('lenght' => 255));
        $table->addColumn('date_upload', 'datetime');
        $table->addColumn('size', 'integer', array('notnull' => true));
        $table->addColumn('hash_name', 'string', array('lenght' => 32));
        $table->addColumn('directory_id', 'integer');
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_directory'), array('directory_id'), array('id'), array("onDelete" => "CASCADE")
        );
        $table->addUniqueIndex(array('hash_name'));
        $this->storeTable($table);
    }

    private function createDirectoryTable(Schema $schema)
    {
        $table = $schema->createTable('claro_directory');

        $this->addId($table);
        $table->addColumn('name', 'string', array('length' => 255));
        $table->addColumn('lft', 'integer', array('notnull' => true));
        $table->addColumn('rgt', 'integer', array('notnull' => true));
        $table->addColumn('lvl', 'integer', array('notnull' => true));
        $table->addColumn('root', 'integer', array('notnull' => false));
        $table->addColumn('parent_id', 'integer', array('notnull' => false));
        $this->storeTable($table);
    }

    /*
      private function createDirectoryDocumentTable(Schema $schema)
      {
      $table = $schema->createTable('claro_directory_document');

      $this->addId($table);
      $table->addColumn('directory_id', 'integer', array('notnull' => false));
      $table->addColumn('document_id', 'integer', array('notnull' => false));

      $table->addForeignKeyConstraint(
      $this->getStoredTable('claro_directory'),
      array('directory_id'), array('id'),
      array("onDelete" => "CASCADE")
      );
      $table->addForeignKeyConstraint(
      $schema->getTable('claro_document'),
      array('document_id'), array('id'),
      array("onDelete" => "CASCADE")
      );
      } */
}
