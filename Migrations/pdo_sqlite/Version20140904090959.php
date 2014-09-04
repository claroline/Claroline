<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/04 09:10:01
 */
class Version20140904090959 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_competence (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                score INTEGER NOT NULL, 
                isPlatform BOOLEAN DEFAULT NULL, 
                code VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F65DE32582D40A1F ON claro_competence (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_competence_hierarchy (
                id INTEGER NOT NULL, 
                competence_id INTEGER NOT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                root INTEGER DEFAULT NULL, 
                lft INTEGER NOT NULL, 
                lvl INTEGER NOT NULL, 
                rgt INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D4A415FD15761DAB ON claro_competence_hierarchy (competence_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D4A415FD727ACA70 ON claro_competence_hierarchy (parent_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX competence_hrch_unique ON claro_competence_hierarchy (competence_id, parent_id)
        ");
        $this->addSql("
            CREATE TABLE claro_competence_users (
                id INTEGER NOT NULL, 
                competence_id INTEGER DEFAULT NULL, 
                user_id INTEGER NOT NULL, 
                score INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_2E80B8E215761DAB ON claro_competence_users (competence_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2E80B8E2A76ED395 ON claro_competence_users (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX competence_user_unique ON claro_competence_users (competence_id, user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_registration_queue (
                id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                workspace_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F461C538D60322AC ON claro_workspace_registration_queue (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F461C538A76ED395 ON claro_workspace_registration_queue (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F461C53882D40A1F ON claro_workspace_registration_queue (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX user_role_unique ON claro_workspace_registration_queue (role_id, user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_security_token (
                id INTEGER NOT NULL, 
                client_name VARCHAR(255) NOT NULL, 
                token VARCHAR(255) NOT NULL, 
                client_ip VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_B3A67A408FBFBD64 ON claro_security_token (client_name)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_competence
        ");
        $this->addSql("
            DROP TABLE claro_competence_hierarchy
        ");
        $this->addSql("
            DROP TABLE claro_competence_users
        ");
        $this->addSql("
            DROP TABLE claro_workspace_registration_queue
        ");
        $this->addSql("
            DROP TABLE claro_security_token
        ");
    }
}