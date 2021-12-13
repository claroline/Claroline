<?php

namespace Claroline\LinkBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/12/08 09:05:39
 */
class Version20211208090537 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_shortcut 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_resource_shortcut SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_5E7F4AB8D17F50A6 ON claro_resource_shortcut (uuid)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX UNIQ_5E7F4AB8D17F50A6 ON claro_resource_shortcut
        ');
        $this->addSql('
            ALTER TABLE claro_resource_shortcut 
            DROP uuid
        ');
    }
}
