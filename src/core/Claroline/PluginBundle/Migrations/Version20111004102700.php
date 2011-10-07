<?php
namespace Claroline\PluginBundle\Migrations;

use Claroline\InstallBundle\Library\Migration\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20111004102700 extends BundleMigration
{
    
    public function up(Schema $schema)
    {
        $this->createAbstractPluginTable($schema);
        $this->createApplicationTable($schema);
        $this->createApplicationLauncherTable($schema);
        $this->createLauncherRoleJoinTable($schema);
        
    }
    
    private function createAbstractPluginTable(Schema $schema)
    {
        $table = $schema->createTable('claro_abstract_plugin');
        $this->addId($table);
        
        $table->addColumn('type', 'string', array('length' => 255));
        $table->addColumn('bundle_fqcn', 'string', array('length' => 255));
        $table->addColumn('vendor_name', 'string', array('length' => 50));
        $table->addColumn('short_name', 'string', array('length' => 50));
        $table->addColumn('name_translation_key', 'string', array('length' => 255));
        $table->addColumn('description', 'string', array('length' => 255));
        $table->addColumn('discr', 'string');
    }
    
    private function createApplicationTable(Schema $schema)
    {
        $table = $schema->createTable('claro_application');
        $this->addId($table);
        
        $table->addColumn('type', 'string', array('length' => 255));
        $table->addColumn('bundle_fqcn', 'string', array('length' => 255));
        $table->addColumn('vendor_name', 'string', array('length' => 50));
        $table->addColumn('short_name', 'string', array('length' => 50));
        $table->addColumn('name_translation_key', 'string', array('length' => 255));
        $table->addColumn('discr', 'string');
    }
    
    private function createApplicationLauncherTable(Schema $schema)
    {
        $table = $schema->createTable('claro_application_launcher');
        $this->addId($table);
        
        $this->addReference($table, 'application');
        $table->addColumn('route_id', 'string', array('length' => 255));
        $table->addColumn('translation_key', 'string', array('length' => 255)); 
    }
    
    private function createLauncherRoleJoinTable(Schema $schema)
    {
        $table = $schema->createTable('claro_launcher_role');
        $this->addReference($table, 'launcher');
        $this->addReference($table, 'role');
    }
    
    

    public function down(Schema $schema)
    {
        $schema->dropTable('claro_abstract_plugin');
        $schema->dropTable('claro_application');
        $schema->dropTable('claro_application_launcher');
        $schema->dropTable('claro_launcher_role');
    }


}
