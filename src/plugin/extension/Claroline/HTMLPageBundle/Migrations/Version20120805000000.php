<?php

namespace Claroline\HTMLPageBundle\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120805000000 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createHTMLElementTable($schema);
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_html_element');;
    }
    
    private function createHTMLElementTable(Schema $schema)
    {
        $table = $schema->createTable('claro_html_element');
        
        $this->addId($table);        
        $table->addColumn('archive', 'string', array('length' => 255));
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_resource'),
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
}