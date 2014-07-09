<?php

namespace Innova\PathBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/13 10:28:47
 */
class Version20140613102846 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD COLUMN activity_id INTEGER DEFAULT NULL 
            ADD COLUMN parameters_id INTEGER DEFAULT NULL 
            DROP COLUMN description 
            DROP COLUMN withTutor 
            DROP COLUMN duration 
            DROP COLUMN stepWho_id 
            DROP COLUMN stepWhere_id 
            DROP COLUMN name
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F4856765544574
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F485678FE76F3
        ");
        $this->addSql("
            DROP INDEX IDX_86F4856765544574
        ");
        $this->addSql("
            DROP INDEX IDX_86F485678FE76F3
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F4856781C06096 FOREIGN KEY (activity_id) 
            REFERENCES claro_activity (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F4856788BD9C1F FOREIGN KEY (parameters_id) 
            REFERENCES claro_activity_parameters (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F4856781C06096 ON innova_step (activity_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F4856788BD9C1F ON innova_step (parameters_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD COLUMN description CLOB(1M) DEFAULT NULL 
            ADD COLUMN withTutor SMALLINT NOT NULL 
            ADD COLUMN duration TIMESTAMP(0) DEFAULT NULL 
            ADD COLUMN stepWho_id INTEGER DEFAULT NULL 
            ADD COLUMN stepWhere_id INTEGER DEFAULT NULL 
            ADD COLUMN name VARCHAR(255) NOT NULL 
            DROP COLUMN activity_id 
            DROP COLUMN parameters_id
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F4856781C06096
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F4856788BD9C1F
        ");
        $this->addSql("
            DROP INDEX IDX_86F4856781C06096
        ");
        $this->addSql("
            DROP INDEX IDX_86F4856788BD9C1F
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
            CREATE INDEX IDX_86F4856765544574 ON innova_step (stepWho_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F485678FE76F3 ON innova_step (stepWhere_id)
        ");
    }
}