<?php

namespace Innova\PathBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/30 03:54:06
 */
class Version20130930155405 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD COLUMN parent_id INTEGER DEFAULT NULL 
            DROP COLUMN parent
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES innova_step (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567727ACA70 ON innova_step (parent_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD COLUMN parent VARCHAR(255) DEFAULT NULL 
            DROP COLUMN parent_id
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F48567727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_86F48567727ACA70
        ");
    }
}