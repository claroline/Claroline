<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/18 11:35:04
 */
class Version20140618113503 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            ADD COLUMN who VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            ADD COLUMN \"where\" VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            ADD COLUMN with_tutor BOOLEAN DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_E2EE25E281C06096
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity_parameters AS 
            SELECT id, 
            activity_id, 
            max_duration, 
            max_attempts, 
            evaluation_type 
            FROM claro_activity_parameters
        ");
        $this->addSql("
            DROP TABLE claro_activity_parameters
        ");
        $this->addSql("
            CREATE TABLE claro_activity_parameters (
                id INTEGER NOT NULL, 
                activity_id INTEGER DEFAULT NULL, 
                max_duration INTEGER DEFAULT NULL, 
                max_attempts INTEGER DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E2EE25E281C06096 FOREIGN KEY (activity_id) 
                REFERENCES claro_activity (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_parameters (
                id, activity_id, max_duration, max_attempts, 
                evaluation_type
            ) 
            SELECT id, 
            activity_id, 
            max_duration, 
            max_attempts, 
            evaluation_type 
            FROM __temp__claro_activity_parameters
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity_parameters
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E2EE25E281C06096 ON claro_activity_parameters (activity_id)
        ");
    }
}