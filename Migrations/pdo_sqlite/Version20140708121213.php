<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/08 12:12:14
 */
class Version20140708121213 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_C7E87ECC73B21E9C
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_step_inherited_resources AS 
            SELECT id, 
            step_id, 
            lvl 
            FROM innova_step_inherited_resources
        ");
        $this->addSql("
            DROP TABLE innova_step_inherited_resources
        ");
        $this->addSql("
            CREATE TABLE innova_step_inherited_resources (
                id INTEGER NOT NULL, 
                step_id INTEGER DEFAULT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                lvl INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_C7E87ECC73B21E9C FOREIGN KEY (step_id) 
                REFERENCES innova_step (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_C7E87ECC89329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_step_inherited_resources (id, step_id, lvl) 
            SELECT id, 
            step_id, 
            lvl 
            FROM __temp__innova_step_inherited_resources
        ");
        $this->addSql("
            DROP TABLE __temp__innova_step_inherited_resources
        ");
        $this->addSql("
            CREATE INDEX IDX_C7E87ECC73B21E9C ON innova_step_inherited_resources (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C7E87ECC89329D25 ON innova_step_inherited_resources (resource_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_C7E87ECC73B21E9C
        ");
        $this->addSql("
            DROP INDEX IDX_C7E87ECC89329D25
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_step_inherited_resources AS 
            SELECT id, 
            step_id, 
            lvl 
            FROM innova_step_inherited_resources
        ");
        $this->addSql("
            DROP TABLE innova_step_inherited_resources
        ");
        $this->addSql("
            CREATE TABLE innova_step_inherited_resources (
                id INTEGER NOT NULL, 
                step_id INTEGER DEFAULT NULL, 
                lvl INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_C7E87ECC73B21E9C FOREIGN KEY (step_id) 
                REFERENCES innova_step (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_step_inherited_resources (id, step_id, lvl) 
            SELECT id, 
            step_id, 
            lvl 
            FROM __temp__innova_step_inherited_resources
        ");
        $this->addSql("
            DROP TABLE __temp__innova_step_inherited_resources
        ");
        $this->addSql("
            CREATE INDEX IDX_C7E87ECC73B21E9C ON innova_step_inherited_resources (step_id)
        ");
    }
}