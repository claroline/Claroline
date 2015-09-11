<?php

namespace FormaLibre\PresenceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/10 09:55:14
 */
class Version20150910095513 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_presencebundle_schoolYear (
                id INT AUTO_INCREMENT NOT NULL, 
                schoolYearName VARCHAR(255) NOT NULL, 
                schoolYear_begin DATE NOT NULL, 
                schoolYear_end DATE NOT NULL, 
                schoolDay_begin_hour TIME NOT NULL, 
                schoolDay_end_hour TIME NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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
            DROP TABLE formalibre_presencebundle_schoolYear
        ");
        $this->addSql("
            DROP INDEX IDX_4E4AE7C08BF32374 ON formalibre_presencebundle_period
        ");
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_period 
            DROP schoolYear_id, 
            CHANGE schoolyearid num_period VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ");
    }
}