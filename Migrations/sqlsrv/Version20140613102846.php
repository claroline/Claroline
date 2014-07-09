<?php

namespace Innova\PathBundle\Migrations\sqlsrv;

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
            ADD activity_id INT
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD parameters_id INT
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN description
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN withTutor
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN duration
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
            DROP COLUMN name
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
            ADD description VARCHAR(MAX)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD withTutor BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD duration DATETIME2(6)
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
            ADD name NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN activity_id
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN parameters_id
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
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_86F4856781C06096'
            ) 
            ALTER TABLE innova_step 
            DROP CONSTRAINT IDX_86F4856781C06096 ELSE 
            DROP INDEX IDX_86F4856781C06096 ON innova_step
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_86F4856788BD9C1F'
            ) 
            ALTER TABLE innova_step 
            DROP CONSTRAINT IDX_86F4856788BD9C1F ELSE 
            DROP INDEX IDX_86F4856788BD9C1F ON innova_step
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