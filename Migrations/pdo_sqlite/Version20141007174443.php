<?php

namespace Claroline\TeamBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/07 05:44:45
 */
class Version20141007174443 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_team (
                id INTEGER NOT NULL, 
                workspace_id INTEGER NOT NULL, 
                role_id INTEGER DEFAULT NULL, 
                team_manager INTEGER DEFAULT NULL, 
                team_manager_role INTEGER DEFAULT NULL, 
                directory_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                max_users INTEGER DEFAULT NULL, 
                self_registration BOOLEAN NOT NULL, 
                self_unregistration BOOLEAN NOT NULL, 
                is_public BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A2FE580482D40A1F ON claro_team (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A2FE5804D60322AC ON claro_team (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A2FE580455D548E ON claro_team (team_manager)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A2FE580459E625D1 ON claro_team (team_manager_role)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A2FE58042C94069F ON claro_team (directory_id)
        ");
        $this->addSql("
            CREATE TABLE claro_team_users (
                team_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                PRIMARY KEY(team_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B10C67F3296CD8AE ON claro_team_users (team_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B10C67F3A76ED395 ON claro_team_users (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_team_parameters (
                id INTEGER NOT NULL, 
                workspace_id INTEGER NOT NULL, 
                self_registration BOOLEAN NOT NULL, 
                self_unregistration BOOLEAN NOT NULL, 
                is_public BOOLEAN NOT NULL, 
                max_teams INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_C99EF54182D40A1F ON claro_team_parameters (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_team
        ");
        $this->addSql("
            DROP TABLE claro_team_users
        ");
        $this->addSql("
            DROP TABLE claro_team_parameters
        ");
    }
}