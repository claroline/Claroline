<?php

namespace Innova\PathBundle\Migrations\oci8;

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
            ADD (
                activity_id NUMBER(10) DEFAULT NULL, 
                parameters_id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP (
                description, withTutor, duration, 
                stepWho_id, stepWhere_id, name
            )
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
            ADD (
                description CLOB DEFAULT NULL, 
                withTutor NUMBER(1) NOT NULL, 
                duration TIMESTAMP(0) DEFAULT NULL, 
                stepWho_id NUMBER(10) DEFAULT NULL, 
                stepWhere_id NUMBER(10) DEFAULT NULL, 
                name VARCHAR2(255) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP (activity_id, parameters_id)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP CONSTRAINT FK_86F4856781C06096
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP CONSTRAINT FK_86F4856788BD9C1F
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