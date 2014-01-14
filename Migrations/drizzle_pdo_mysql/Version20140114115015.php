<?php

namespace Innova\PathBundle\Migrations\drizzle_pdo_mysql;

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
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F48567DEDC9FF6
        ");
        $this->addSql("
            DROP INDEX IDX_86F48567DEDC9FF6 ON innova_step
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP expanded, 
            DROP stepType_id, 
            CHANGE duration duration DATETIME DEFAULT NULL, 
            CHANGE instructions description TEXT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD expanded BOOLEAN NOT NULL, 
            ADD stepType_id INT DEFAULT NULL, 
            CHANGE duration duration DATETIME NOT NULL, 
            CHANGE description instructions TEXT DEFAULT NULL
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