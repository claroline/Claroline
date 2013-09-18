<?php

namespace Icap\WikiBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/02 11:26:42
 */
class Version20130902112641 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__wikibundle_section (
                id INT AUTO_INCREMENT NOT NULL, 
                wiki_id INT NOT NULL, 
                parent_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                text LONGTEXT DEFAULT NULL, 
                created DATETIME NOT NULL, 
                INDEX IDX_F79A2D04AA948DBE (wiki_id), 
                INDEX IDX_F79A2D04727ACA70 (parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__wikibundle_wiki (
                id INT AUTO_INCREMENT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_31A29422B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE icap__wikibundle_section 
            ADD CONSTRAINT FK_F79A2D04AA948DBE FOREIGN KEY (wiki_id) 
            REFERENCES icap__wikibundle_wiki (id)
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
            DROP FOREIGN KEY FK_F79A2D04727ACA70
        ");
        $this->addSql("
            ALTER TABLE icap__wikibundle_section 
            DROP FOREIGN KEY FK_F79A2D04AA948DBE
        ");
        $this->addSql("
            DROP TABLE icap__wikibundle_section
        ");
        $this->addSql("
            DROP TABLE icap__wikibundle_wiki
        ");
    }
}