<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/09/29 05:56:43
 */
class Version20170929175642 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_group
            CHANGE `guid` `uuid`
            VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_user
            CHANGE `guid` `uuid`
            VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_role
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_role SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_EB8D2852D17F50A6 ON claro_user (uuid)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_E7C393D7D17F50A6 ON claro_group (uuid)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_31777471D17F50A6 ON claro_role (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_group
            CHANGE `uuid` `guid`
            VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_user
            CHANGE `uuid` `guid`
            VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_role
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_E7C393D7D17F50A6 ON claro_group
        ');
        $this->addSql('
            DROP INDEX UNIQ_31777471D17F50A6 ON claro_role
        ');
        $this->addSql('
            DROP INDEX UNIQ_EB8D2852D17F50A6 ON claro_user
        ');
    }
}
