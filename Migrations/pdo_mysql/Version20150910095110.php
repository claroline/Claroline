<?php

namespace FormaLibre\PresenceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/10 09:51:14
 */
class Version20150910095110 extends AbstractMigration
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
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE formalibre_presencebundle_schoolYear
        ");
    }
}