<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/06/10 07:35:42
 */
class Version20200610073540 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP closable, 
            DROP closeTarget
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            ADD showIcon TINYINT(1) DEFAULT "0" NOT NULL, 
            ADD thumbnail VARCHAR(255) DEFAULT NULL, 
            ADD poster VARCHAR(255) DEFAULT NULL, 
            DROP is_locked
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            ADD is_locked TINYINT(1) NOT NULL, 
            DROP showIcon, 
            DROP thumbnail, 
            DROP poster
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD closable TINYINT(1) NOT NULL, 
            ADD closeTarget INT NOT NULL
        ');
    }
}
