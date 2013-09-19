<?php

namespace Innova\PathBundle\Migrations\pdo_pgsql;

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
            ADD stepType_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD stepWho_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD stepWhere_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567DEDC9FF6 FOREIGN KEY (stepType_id) 
            REFERENCES innova_stepType (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F4856765544574 FOREIGN KEY (stepWho_id) 
            REFERENCES innova_stepWho (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F485678FE76F3 FOREIGN KEY (stepWhere_id) 
            REFERENCES innova_stepWhere (id) NOT DEFERRABLE INITIALLY IMMEDIATE
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
            DROP stepType_id
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP stepWho_id
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP stepWhere_id
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
            DROP INDEX IDX_86F48567DEDC9FF6
        ");
        $this->addSql("
            DROP INDEX IDX_86F4856765544574
        ");
        $this->addSql("
            DROP INDEX IDX_86F485678FE76F3
        ");
    }
}