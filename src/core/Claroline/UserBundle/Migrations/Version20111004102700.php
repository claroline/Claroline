<?php
namespace Claroline\UserBundle\Migrations;

use Claroline\InstallBundle\Library\Migration\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20111004102700 extends BundleMigration
{
    
    public function up(Schema $schema)
    {
        $this->createUserTable($schema);
        $this->createUserRoleJoinTable($schema);
    }
    
    private function createUserTable(Schema $schema)
    {
        $table = $schema->createTable('claro_user');
        $this->addId($table);
        
        $table->addColumn('first_name', 'string', array('length' => 50));
        $table->addColumn('last_name', 'string', array('length' => 50));
        $table->addColumn('username', 'string', array('length' => 255));
        $table->addColumn('password', 'string', array('length' => 255));
        $table->addColumn('salt', 'string', array('length' => 255));
    
        $table->addUniqueIndex(array('username'));
    }
    
    private function createUserRoleJoinTable(Schema $schema)
    {
        $table = $schema->createTable('claro_user_role');
        
        $this->addReference($table, 'user');
        $this->addReference($table, 'role');
        
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_user');
        $schema->dropTable('claro_user_role');
    }


}
