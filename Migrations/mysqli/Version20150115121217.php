<?php

namespace Claroline\CursusBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/01/15 12:12:20
 */
class Version20150115121217 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_course (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_cursus (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_cursus_displayed_word (
                id INT AUTO_INCREMENT NOT NULL, 
                word VARCHAR(255) NOT NULL, 
                displayed_name VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_62A6D87CC3F17511 (word), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_course
        ");
        $this->addSql("
            DROP TABLE claro_cursus
        ");
        $this->addSql("
            DROP TABLE claro_cursus_displayed_word
        ");
    }
}