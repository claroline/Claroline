<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/02 02:05:21
 */
class Version20140902140520 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_86F48567727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_86F48567D96C566B
        ");
        $this->addSql("
            DROP INDEX IDX_86F4856781C06096
        ");
        $this->addSql("
            DROP INDEX IDX_86F4856788BD9C1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_step AS 
            SELECT id, 
            parent_id, 
            activity_id, 
            parameters_id, 
            path_id, 
            step_order, 
            lvl 
            FROM innova_step
        ");
        $this->addSql("
            DROP TABLE innova_step
        ");
        $this->addSql("
            CREATE TABLE innova_step (
                id INTEGER NOT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                activity_id INTEGER DEFAULT NULL, 
                parameters_id INTEGER DEFAULT NULL, 
                path_id INTEGER DEFAULT NULL, 
                step_order INTEGER NOT NULL, 
                lvl INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_86F48567727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES innova_step (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F4856781C06096 FOREIGN KEY (activity_id) 
                REFERENCES claro_activity (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F4856788BD9C1F FOREIGN KEY (parameters_id) 
                REFERENCES claro_activity_parameters (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F48567D96C566B FOREIGN KEY (path_id) 
                REFERENCES innova_path (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_step (
                id, parent_id, activity_id, parameters_id, 
                path_id, step_order, lvl
            ) 
            SELECT id, 
            parent_id, 
            activity_id, 
            parameters_id, 
            path_id, 
            step_order, 
            lvl 
            FROM __temp__innova_step
        ");
        $this->addSql("
            DROP TABLE __temp__innova_step
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567727ACA70 ON innova_step (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567D96C566B ON innova_step (path_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F4856781C06096 ON innova_step (activity_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F4856788BD9C1F ON innova_step (parameters_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_86F4856781C06096
        ");
        $this->addSql("
            DROP INDEX IDX_86F4856788BD9C1F
        ");
        $this->addSql("
            DROP INDEX IDX_86F48567727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_86F48567D96C566B
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_step AS 
            SELECT id, 
            activity_id, 
            parameters_id, 
            parent_id, 
            path_id, 
            lvl, 
            step_order 
            FROM innova_step
        ");
        $this->addSql("
            DROP TABLE innova_step
        ");
        $this->addSql("
            CREATE TABLE innova_step (
                id INTEGER NOT NULL, 
                activity_id INTEGER DEFAULT NULL, 
                parameters_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                path_id INTEGER DEFAULT NULL, 
                lvl INTEGER NOT NULL, 
                step_order INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_86F4856781C06096 FOREIGN KEY (activity_id) 
                REFERENCES claro_activity (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F4856788BD9C1F FOREIGN KEY (parameters_id) 
                REFERENCES claro_activity_parameters (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F48567727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES innova_step (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F48567D96C566B FOREIGN KEY (path_id) 
                REFERENCES innova_path (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_step (
                id, activity_id, parameters_id, parent_id, 
                path_id, lvl, step_order
            ) 
            SELECT id, 
            activity_id, 
            parameters_id, 
            parent_id, 
            path_id, 
            lvl, 
            step_order 
            FROM __temp__innova_step
        ");
        $this->addSql("
            DROP TABLE __temp__innova_step
        ");
        $this->addSql("
            CREATE INDEX IDX_86F4856781C06096 ON innova_step (activity_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F4856788BD9C1F ON innova_step (parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567727ACA70 ON innova_step (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567D96C566B ON innova_step (path_id)
        ");
    }
}