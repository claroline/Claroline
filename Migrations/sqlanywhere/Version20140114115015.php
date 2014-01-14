<?php

namespace Innova\PathBundle\Migrations\sqlanywhere;

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
            ALTER TABLE innova_step RENAME instructions TO description
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP expanded, 
            DROP stepType_id, 
            ALTER duration DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F48567DEDC9FF6
        ");
        $this->addSql("
            DROP INDEX innova_step.IDX_86F48567DEDC9FF6
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step RENAME description TO instructions
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD expanded BIT NOT NULL, 
            ADD stepType_id INT DEFAULT NULL, 
            ALTER duration DATETIME NOT NULL
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