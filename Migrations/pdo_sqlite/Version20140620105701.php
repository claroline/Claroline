<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/20 10:57:02
 */
class Version20140620105701 extends AbstractMigration
{
    public function up(Schema $schema)
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
            evaluation_type, 
            who, 
            \"where\", 
            with_tutor 
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
                who VARCHAR(255) DEFAULT NULL, 
                with_tutor BOOLEAN DEFAULT NULL, 
                activity_where VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E2EE25E281C06096 FOREIGN KEY (activity_id) 
                REFERENCES claro_activity (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_parameters (
                id, activity_id, max_duration, max_attempts, 
                evaluation_type, who, activity_where, 
                with_tutor
            ) 
            SELECT id, 
            activity_id, 
            max_duration, 
            max_attempts, 
            evaluation_type, 
            who, 
            \"where\", 
            with_tutor 
            FROM __temp__claro_activity_parameters
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity_parameters
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E2EE25E281C06096 ON claro_activity_parameters (activity_id)
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
            who, 
            activity_where, 
            with_tutor, 
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
                who VARCHAR(255) DEFAULT NULL, 
                with_tutor BOOLEAN DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                \"where\" VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E2EE25E281C06096 FOREIGN KEY (activity_id) 
                REFERENCES claro_activity (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_parameters (
                id, activity_id, max_duration, max_attempts, 
                who, \"where\", with_tutor, evaluation_type
            ) 
            SELECT id, 
            activity_id, 
            max_duration, 
            max_attempts, 
            who, 
            activity_where, 
            with_tutor, 
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