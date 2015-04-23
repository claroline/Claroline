<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/09 10:53:52
 */
class Version20150309105351 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node ALTER value TYPE INT
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node ALTER value 
            DROP DEFAULT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node ALTER value TYPE VARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node ALTER value 
            DROP DEFAULT
        ");
    }
}