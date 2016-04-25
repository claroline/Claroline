<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/10/07 03:40:28
 */
class Version20151007154027 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('SET foreign_key_checks = 0');
        $this->addSql('
            ALTER TABLE claro_user 
            ADD guid VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_group 
            ADD guid VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD guid VARCHAR(255) NOT NULL
        ');
        $this->addSql('SET foreign_key_checks = 1');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_E7C393D72B6FCFB2 ON claro_group
        ');
        $this->addSql('
            ALTER TABLE claro_group 
            DROP guid
        ');
        $this->addSql('
            DROP INDEX UNIQ_A76799FF2B6FCFB2 ON claro_resource_node
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP guid
        ');
        $this->addSql('
            DROP INDEX UNIQ_EB8D28522B6FCFB2 ON claro_user
        ');
        $this->addSql('
            ALTER TABLE claro_user 
            DROP guid
        ');
    }
}
