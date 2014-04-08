<?php

namespace UJM\ExoBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/08 09:32:27
 */
class Version20140408093225 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__ujm_type_qcm AS 
            SELECT id, 
            value 
            FROM ujm_type_qcm
        ");
        $this->addSql("
            DROP TABLE ujm_type_qcm
        ");
        $this->addSql("
            CREATE TABLE ujm_type_qcm (
                id INTEGER NOT NULL, 
                value VARCHAR(255) NOT NULL, 
                code INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO ujm_type_qcm (id, value) 
            SELECT id, 
            value 
            FROM __temp__ujm_type_qcm
        ");
        $this->addSql("
            DROP TABLE __temp__ujm_type_qcm
        ");
        
        $this->addSql("
            UPDATE ujm_type_qcm SET code=1 WHERE value='Multiple response'
        ");
        $this->addSql("
            UPDATE ujm_type_qcm SET code=2 WHERE value='Unique response'
        ");
        
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_4C21382C77153098 ON ujm_type_qcm (code)
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__ujm_type_open_question AS 
            SELECT id, 
            value 
            FROM ujm_type_open_question
        ");
        $this->addSql("
            DROP TABLE ujm_type_open_question
        ");
        $this->addSql("
            CREATE TABLE ujm_type_open_question (
                id INTEGER NOT NULL, 
                value VARCHAR(255) NOT NULL, 
                code INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO ujm_type_open_question (id, value) 
            SELECT id, 
            value 
            FROM __temp__ujm_type_open_question
        ");
        $this->addSql("
            DROP TABLE __temp__ujm_type_open_question
        ");
        
        $this->addSql("
            UPDATE ujm_type_open_question SET code=1 WHERE value='numerical'
        ");
        $this->addSql("
            UPDATE ujm_type_open_question SET code=2 WHERE value= 'long'
        ");
        $this->addSql("
            UPDATE ujm_type_open_question SET code=3 WHERE value='short'
        ");
        $this->addSql("
            UPDATE ujm_type_open_question SET code=4 WHERE value='oneWord'
        ");
        
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_ABC1CC4777153098 ON ujm_type_open_question (code)
        ");
        
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_ABC1CC4777153098
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__ujm_type_open_question AS 
            SELECT id, 
            value 
            FROM ujm_type_open_question
        ");
        $this->addSql("
            DROP TABLE ujm_type_open_question
        ");
        $this->addSql("
            CREATE TABLE ujm_type_open_question (
                id INTEGER NOT NULL, 
                value VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO ujm_type_open_question (id, value) 
            SELECT id, 
            value 
            FROM __temp__ujm_type_open_question
        ");
        $this->addSql("
            DROP TABLE __temp__ujm_type_open_question
        ");
        $this->addSql("
            DROP INDEX UNIQ_4C21382C77153098
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__ujm_type_qcm AS 
            SELECT id, 
            value 
            FROM ujm_type_qcm
        ");
        $this->addSql("
            DROP TABLE ujm_type_qcm
        ");
        $this->addSql("
            CREATE TABLE ujm_type_qcm (
                id INTEGER NOT NULL, 
                value VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO ujm_type_qcm (id, value) 
            SELECT id, 
            value 
            FROM __temp__ujm_type_qcm
        ");
        $this->addSql("
            DROP TABLE __temp__ujm_type_qcm
        ");
    }
}