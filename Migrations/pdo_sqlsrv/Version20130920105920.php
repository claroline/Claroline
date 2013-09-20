<?php

namespace Icap\WikiBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/20 10:59:21
 */
class Version20130920105920 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__wikibundle_section (
                id INT IDENTITY NOT NULL, 
                wiki_id INT NOT NULL, 
                parent_id INT, 
                name NVARCHAR(255) NOT NULL, 
                visible BIT NOT NULL, 
                text VARCHAR(MAX), 
                created DATETIME2(6) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F79A2D04AA948DBE ON icap__wikibundle_section (wiki_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F79A2D04727ACA70 ON icap__wikibundle_section (parent_id)
        ");
        $this->addSql("
            CREATE TABLE icap__wikibundle_wiki (
                id INT IDENTITY NOT NULL, 
                text VARCHAR(MAX), 
                resourceNode_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_31A29422B87FAB32 ON icap__wikibundle_wiki (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__wikibundle_section 
            ADD CONSTRAINT FK_F79A2D04AA948DBE FOREIGN KEY (wiki_id) 
            REFERENCES icap__wikibundle_wiki (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__wikibundle_section 
            ADD CONSTRAINT FK_F79A2D04727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES icap__wikibundle_section (id)
        ");
        $this->addSql("
            ALTER TABLE icap__wikibundle_wiki 
            ADD CONSTRAINT FK_31A29422B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__wikibundle_section 
            DROP CONSTRAINT FK_F79A2D04727ACA70
        ");
        $this->addSql("
            ALTER TABLE icap__wikibundle_section 
            DROP CONSTRAINT FK_F79A2D04AA948DBE
        ");
        $this->addSql("
            DROP TABLE icap__wikibundle_section
        ");
        $this->addSql("
            DROP TABLE icap__wikibundle_wiki
        ");
    }
}