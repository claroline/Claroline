<?php

namespace Icap\WikiBundle\Migrations\pdo_sqlsrv;

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
                id INT IDENTITY NOT NULL, 
                user_id INT, 
                wiki_id INT NOT NULL, 
                parent_id INT, 
                title NVARCHAR(255), 
                visible BIT NOT NULL, 
                text VARCHAR(MAX), 
                creation_date DATETIME2(6) NOT NULL, 
                modification_date DATETIME2(6) NOT NULL, 
                lft INT NOT NULL, 
                lvl INT NOT NULL, 
                rgt INT NOT NULL, 
                root INT, 
                PRIMARY KEY (id)
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
                id INT IDENTITY NOT NULL, 
                root_id INT, 
                resourceNode_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_1FAD6B8179066886 ON icap__wiki (root_id) 
            WHERE root_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_1FAD6B81B87FAB32 ON icap__wiki (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AAAA948DBE FOREIGN KEY (wiki_id) 
            REFERENCES icap__wiki (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            ADD CONSTRAINT FK_82904AA727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES icap__wiki_section (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__wiki 
            ADD CONSTRAINT FK_1FAD6B8179066886 FOREIGN KEY (root_id) 
            REFERENCES icap__wiki_section (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__wiki 
            ADD CONSTRAINT FK_1FAD6B81B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            DROP CONSTRAINT FK_82904AA727ACA70
        ");
        $this->addSql("
            ALTER TABLE icap__wiki 
            DROP CONSTRAINT FK_1FAD6B8179066886
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            DROP CONSTRAINT FK_82904AAAA948DBE
        ");
        $this->addSql("
            DROP TABLE icap__wiki_section
        ");
        $this->addSql("
            DROP TABLE icap__wiki
        ");
    }
}