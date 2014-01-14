<?php

namespace Innova\PathBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/14 11:50:16
 */
class Version20140114115015 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step MODIFY (
                duration TIMESTAMP(0) DEFAULT NULL NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step RENAME COLUMN instructions TO description
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP (expanded, stepType_id)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP CONSTRAINT FK_86F48567DEDC9FF6
        ");
        $this->addSql("
            DROP INDEX IDX_86F48567DEDC9FF6
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD (
                expanded NUMBER(1) NOT NULL, 
                stepType_id NUMBER(10) DEFAULT NULL NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step MODIFY (
                duration TIMESTAMP(0) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step RENAME COLUMN description TO instructions
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567DEDC9FF6 FOREIGN KEY (stepType_id) 
            REFERENCES innova_stepType (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567DEDC9FF6 ON innova_step (stepType_id)
        ");
    }
}