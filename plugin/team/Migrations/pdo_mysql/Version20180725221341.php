<?php

namespace Claroline\TeamBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/07/25 10:13:43
 */
class Version20180725221341 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_team 
            ADD uuid VARCHAR(36) NOT NULL,
            ADD dir_deletable TINYINT(1) DEFAULT '0' NOT NULL
        ");
        $this->addSql('
            UPDATE claro_team SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_A2FE5804D17F50A6 ON claro_team (uuid)
        ');
        $this->addSql("
            ALTER TABLE claro_team_parameters 
            ADD uuid VARCHAR(36) NOT NULL,
            ADD dir_deletable TINYINT(1) DEFAULT '0' NOT NULL
        ");
        $this->addSql('
            UPDATE claro_team_parameters SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_C99EF541D17F50A6 ON claro_team_parameters (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_A2FE5804D17F50A6 ON claro_team
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            DROP uuid,
            DROP dir_deletable
        ');
        $this->addSql('
            DROP INDEX UNIQ_C99EF541D17F50A6 ON claro_team_parameters
        ');
        $this->addSql('
            ALTER TABLE claro_team_parameters 
            DROP uuid,
            DROP dir_deletable
        ');
    }
}
