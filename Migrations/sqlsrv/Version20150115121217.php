<?php

namespace Claroline\CursusBundle\Migrations\sqlsrv;

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
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                description VARCHAR(MAX), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_cursus (
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                description VARCHAR(MAX), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_cursus_displayed_word (
                id INT IDENTITY NOT NULL, 
                word NVARCHAR(255) NOT NULL, 
                displayed_name NVARCHAR(255), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_62A6D87CC3F17511 ON claro_cursus_displayed_word (word) 
            WHERE word IS NOT NULL
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