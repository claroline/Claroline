<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/11/15 10:40:03
 */
class Version20131115104001 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_8BE678285E237E06
        ');
        $this->addSql('
            CREATE TEMPORARY TABLE __temp__icap__blog_tag AS 
            SELECT id, 
            name 
            FROM icap__blog_tag
        ');
        $this->addSql('
            DROP TABLE icap__blog_tag
        ');
        $this->addSql('
            CREATE TABLE icap__blog_tag (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                slug VARCHAR(128) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('
            INSERT INTO icap__blog_tag (id, name) 
            SELECT id, 
            name 
            FROM __temp__icap__blog_tag
        ');
        $this->addSql('
            DROP TABLE __temp__icap__blog_tag
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_8BE678285E237E06 ON icap__blog_tag (name)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_8BE67828989D9B62 ON icap__blog_tag (slug)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_8BE678285E237E06
        ');
        $this->addSql('
            DROP INDEX UNIQ_8BE67828989D9B62
        ');
        $this->addSql('
            CREATE TEMPORARY TABLE __temp__icap__blog_tag AS 
            SELECT id, 
            name 
            FROM icap__blog_tag
        ');
        $this->addSql('
            DROP TABLE icap__blog_tag
        ');
        $this->addSql('
            CREATE TABLE icap__blog_tag (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('
            INSERT INTO icap__blog_tag (id, name) 
            SELECT id, 
            name 
            FROM __temp__icap__blog_tag
        ');
        $this->addSql('
            DROP TABLE __temp__icap__blog_tag
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_8BE678285E237E06 ON icap__blog_tag (name)
        ');
    }
}
