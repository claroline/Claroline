<?php

namespace Innova\PathBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/19 01:13:04
 */
class Version20130919131304 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD stepType_id INT
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD stepWho_id INT
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD stepWhere_id INT
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567DEDC9FF6 FOREIGN KEY (stepType_id) 
            REFERENCES innova_stepType (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F4856765544574 FOREIGN KEY (stepWho_id) 
            REFERENCES innova_stepWho (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F485678FE76F3 FOREIGN KEY (stepWhere_id) 
            REFERENCES innova_stepWhere (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567DEDC9FF6 ON innova_step (stepType_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F4856765544574 ON innova_step (stepWho_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F485678FE76F3 ON innova_step (stepWhere_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN stepType_id
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN stepWho_id
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN stepWhere_id
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP CONSTRAINT FK_86F48567DEDC9FF6
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP CONSTRAINT FK_86F4856765544574
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP CONSTRAINT FK_86F485678FE76F3
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_86F48567DEDC9FF6'
            ) 
            ALTER TABLE innova_step 
            DROP CONSTRAINT IDX_86F48567DEDC9FF6 ELSE 
            DROP INDEX IDX_86F48567DEDC9FF6 ON innova_step
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_86F4856765544574'
            ) 
            ALTER TABLE innova_step 
            DROP CONSTRAINT IDX_86F4856765544574 ELSE 
            DROP INDEX IDX_86F4856765544574 ON innova_step
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_86F485678FE76F3'
            ) 
            ALTER TABLE innova_step 
            DROP CONSTRAINT IDX_86F485678FE76F3 ELSE 
            DROP INDEX IDX_86F485678FE76F3 ON innova_step
        ");
    }
}