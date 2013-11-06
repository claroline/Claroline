<?php

namespace Icap\WikiBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/11/04 02:06:47
 */
class Version20131104140647 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            ADD COLUMN deleted BOOLEAN DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            ADD COLUMN deletion_date DATETIME DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_82904AAA76ED395
        ");
        $this->addSql("
            DROP INDEX UNIQ_82904AAFE665925
        ");
        $this->addSql("
            DROP INDEX IDX_82904AAAA948DBE
        ");
        $this->addSql("
            DROP INDEX IDX_82904AA727ACA70
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__wiki_section AS 
            SELECT id, 
            user_id, 
            active_contribution_id, 
            wiki_id, 
            parent_id, 
            visible, 
            creation_date, 
            lft, 
            lvl, 
            rgt, 
            root 
            FROM icap__wiki_section
        ");
        $this->addSql("
            DROP TABLE icap__wiki_section
        ");
        $this->addSql("
            CREATE TABLE icap__wiki_section (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                active_contribution_id INTEGER DEFAULT NULL, 
                wiki_id INTEGER NOT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                visible BOOLEAN NOT NULL, 
                creation_date DATETIME NOT NULL, 
                lft INTEGER NOT NULL, 
                lvl INTEGER NOT NULL, 
                rgt INTEGER NOT NULL, 
                root INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_82904AAA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_82904AAFE665925 FOREIGN KEY (active_contribution_id) 
                REFERENCES icap__wiki_contribution (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_82904AAAA948DBE FOREIGN KEY (wiki_id) 
                REFERENCES icap__wiki (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_82904AA727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES icap__wiki_section (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__wiki_section (
                id, user_id, active_contribution_id, 
                wiki_id, parent_id, visible, creation_date, 
                lft, lvl, rgt, root
            ) 
            SELECT id, 
            user_id, 
            active_contribution_id, 
            wiki_id, 
            parent_id, 
            visible, 
            creation_date, 
            lft, 
            lvl, 
            rgt, 
            root 
            FROM __temp__icap__wiki_section
        ");
        $this->addSql("
            DROP TABLE __temp__icap__wiki_section
        ");
        $this->addSql("
            CREATE INDEX IDX_82904AAA76ED395 ON icap__wiki_section (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_82904AAFE665925 ON icap__wiki_section (active_contribution_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_82904AAAA948DBE ON icap__wiki_section (wiki_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_82904AA727ACA70 ON icap__wiki_section (parent_id)
        ");
    }
}