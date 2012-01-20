<?php

namespace Claroline\CoreBundle\Tests\Stub\Migrations;

use Claroline\CoreBundle\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120119000002 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createSpecificResourceTable($schema);
    }
    
    private function createSpecificResourceTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_specific_resource');
        $this->addId($table);
        $table->addColumn('content', 'string', array('length' => 255));  
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_resource'),
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );     
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_test_specific_resource');
    }
}
