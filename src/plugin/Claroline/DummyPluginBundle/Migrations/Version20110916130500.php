<?php
namespace Claroline\DummyPluginBundle\Migrations;

use Claroline\PluginBundle\Migration\PluginMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20110916130500 extends PluginMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->getTable($this->prefix() . '_stuffs');
        $table->addColumn(
            'last_modified',
            'datetime'
        );
        
        
    }

    public function down(Schema $schema)
    {
        $table = $schema->getTable($this->prefix() . '_stuffs');
        $table->dropColumn('last_modified');
    }


}
