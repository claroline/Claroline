<?php

namespace FormaLibre\PresenceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/08/11 10:46:11
 */
class Version20150811104610 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_presencebundle_status (
                id INT AUTO_INCREMENT NOT NULL, 
                statusName VARCHAR(255) NOT NULL, 
                statusColor VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_period CHANGE school_day school_day VARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE formalibre_presencebundle_status
        ");
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_period CHANGE school_day school_day DATE NOT NULL
        ");
    }
}