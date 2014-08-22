<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/14 09:31:21
 */
class Version20140714093119 extends AbstractMigration
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
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_competence
        ");
        $this->addSql("
            DROP TABLE claro_competence_users
        ");
        $this->addSql("
            DROP TABLE claro_competence_hierarchy
        ");
    }
}