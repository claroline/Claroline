<?php
namespace Claroline\SecurityBundle\Migrations;

use Claroline\InstallBundle\Library\Migration\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20111004102700 extends BundleMigration
{
    
    public function up(Schema $schema)
    {
        $this->createRoleTable($schema);
        
    }
    
    private function createRoleTable(Schema $schema)
    {
        $table = $schema->createTable('claro_role');
        $this->addId($table);
        
        $table->addColumn('name', 'string', array('length' => 255));
    }
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_role');
    }


}
