<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/10/02 10:20:35
 */
class Version20171002102034 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__organization
            ADD uuid VARCHAR(36) NOT NULL,
            ADD code VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro__organization SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            UPDATE claro__organization SET code = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_B68DD0D5D17F50A6 ON claro__organization (uuid)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_B68DD0D577153098 ON claro__organization (code)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_B68DD0D5D17F50A6 ON claro__organization
        ');
        $this->addSql('
            DROP INDEX UNIQ_B68DD0D577153098 ON claro__organization
        ');
        $this->addSql('
            ALTER TABLE claro__organization
            DROP uuid,
            DROP code
        ');
    }
}
