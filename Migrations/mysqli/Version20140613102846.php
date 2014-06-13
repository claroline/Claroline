<?php

namespace Innova\PathBundle\Migrations\mysqli;

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
            DROP FOREIGN KEY FK_86F4856765544574
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F485678FE76F3
        ");
        $this->addSql("
            DROP INDEX IDX_86F4856765544574 ON innova_step
        ");
        $this->addSql("
            DROP INDEX IDX_86F485678FE76F3 ON innova_step
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD activity_id INT DEFAULT NULL, 
            ADD parameters_id INT DEFAULT NULL, 
            DROP description, 
            DROP withTutor, 
            DROP duration, 
            DROP stepWho_id, 
            DROP stepWhere_id, 
            DROP name
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
            DROP FOREIGN KEY FK_86F4856781C06096
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F4856788BD9C1F
        ");
        $this->addSql("
            DROP INDEX IDX_86F4856781C06096 ON innova_step
        ");
        $this->addSql("
            DROP INDEX IDX_86F4856788BD9C1F ON innova_step
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD description LONGTEXT DEFAULT NULL, 
            ADD withTutor TINYINT(1) NOT NULL, 
            ADD duration DATETIME DEFAULT NULL, 
            ADD stepWho_id INT DEFAULT NULL, 
            ADD stepWhere_id INT DEFAULT NULL, 
            ADD name VARCHAR(255) NOT NULL, 
            DROP activity_id, 
            DROP parameters_id
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