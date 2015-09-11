<?php

namespace FormaLibre\PresenceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/10 09:57:34
 */
class Version20150910095733 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_schoolYear 
            ADD schoolYearActual TINYINT(1) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_period 
            ADD schoolYear_id INT DEFAULT NULL, 
            CHANGE num_period schoolYearId VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_period 
            ADD CONSTRAINT FK_4E4AE7C08BF32374 FOREIGN KEY (schoolYear_id) 
            REFERENCES formalibre_presencebundle_schoolYear (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_4E4AE7C08BF32374 ON formalibre_presencebundle_period (schoolYear_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_period 
            DROP FOREIGN KEY FK_4E4AE7C08BF32374
        ");
        $this->addSql("
            DROP INDEX IDX_4E4AE7C08BF32374 ON formalibre_presencebundle_period
        ");
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_period 
            DROP schoolYear_id, 
            CHANGE schoolyearid num_period VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ");
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_schoolYear 
            DROP schoolYearActual
        ");
    }
}