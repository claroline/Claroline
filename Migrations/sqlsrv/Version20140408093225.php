<?php

namespace UJM\ExoBundle\Migrations\sqlsrv;

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
            ALTER TABLE ujm_type_qcm 
            ADD code INT NOT NULL
        ");
        
        $this->addSql("
            UPDATE ujm_type_qcm SET code=1 WHERE value='Multiple response'
        ");
        $this->addSql("
            UPDATE ujm_type_qcm SET code=2 WHERE value='Unique response'
        ");
        
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_4C21382C77153098 ON ujm_type_qcm (code) 
            WHERE code IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_type_open_question 
            ADD code INT NOT NULL
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
            WHERE code IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_type_open_question 
            DROP COLUMN code
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_ABC1CC4777153098'
            ) 
            ALTER TABLE ujm_type_open_question 
            DROP CONSTRAINT UNIQ_ABC1CC4777153098 ELSE 
            DROP INDEX UNIQ_ABC1CC4777153098 ON ujm_type_open_question
        ");
        $this->addSql("
            ALTER TABLE ujm_type_qcm 
            DROP COLUMN code
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_4C21382C77153098'
            ) 
            ALTER TABLE ujm_type_qcm 
            DROP CONSTRAINT UNIQ_4C21382C77153098 ELSE 
            DROP INDEX UNIQ_4C21382C77153098 ON ujm_type_qcm
        ");
    }
}