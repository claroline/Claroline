<?php

namespace UJM\ExoBundle\Migrations\pdo_ibm;

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
            ADD COLUMN code INTEGER NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_4C21382C77153098 ON ujm_type_qcm (code)
        ");
        $this->addSql("
            ALTER TABLE ujm_type_open_question 
            ADD COLUMN code INTEGER NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_ABC1CC4777153098 ON ujm_type_open_question (code)
        ");
        
        $this->addSql("
            UPDATE ujm_type_qcm SET code=1 WHERE value='Multiple response'
        ");
        $this->addSql("
            UPDATE ujm_type_qcm SET code=2 WHERE value='Unique response'
        ");
       
        $this->addSql("
            UPDATE ujm_type_open_question SET code=1 WHERE value='numerical'
        ");
        $this->addSql("
            UPDATE ujm_type_open_question SET code=2 WHERE value= long'
        ");
        $this->addSql("
            UPDATE ujm_type_open_question SET code=3 WHERE value='short'
        ");
        $this->addSql("
            UPDATE ujm_type_open_question SET code=4 WHERE value='oneWord'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_type_open_question 
            DROP COLUMN code
        ");
        $this->addSql("
            DROP INDEX UNIQ_ABC1CC4777153098
        ");
        $this->addSql("
            ALTER TABLE ujm_type_qcm 
            DROP COLUMN code
        ");
        $this->addSql("
            DROP INDEX UNIQ_4C21382C77153098
        ");
    }
}