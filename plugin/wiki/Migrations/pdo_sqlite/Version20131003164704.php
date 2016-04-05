<?php

namespace Icap\WikiBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/03 04:47:05
 */
class Version20131003164704 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__wiki_section (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                wiki_id INTEGER NOT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                visible BOOLEAN NOT NULL, 
                text CLOB DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                modification_date DATETIME NOT NULL, 
                lft INTEGER NOT NULL, 
                lvl INTEGER NOT NULL, 
                rgt INTEGER NOT NULL, 
                root INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_82904AAA76ED395 ON icap__wiki_section (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_82904AAAA948DBE ON icap__wiki_section (wiki_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_82904AA727ACA70 ON icap__wiki_section (parent_id)
        ");
        $this->addSql("
            CREATE TABLE icap__wiki (
                id INTEGER NOT NULL, 
                root_id INTEGER DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_1FAD6B8179066886 ON icap__wiki (root_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_1FAD6B81B87FAB32 ON icap__wiki (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__wiki_section
        ");
        $this->addSql("
            DROP TABLE icap__wiki
        ");
    }
}