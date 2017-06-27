<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/06/20 04:13:57
 */
class Version20170620161356 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_icon
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_resource_icon
            SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_478C5861D17F50A6 ON claro_resource_icon (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_478C5861D17F50A6 ON claro_resource_icon
        ');
        $this->addSql('
            ALTER TABLE claro_resource_icon
            DROP uuid
        ');
    }
}
