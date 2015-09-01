<?php

namespace Claroline\TagBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/01 04:13:35
 */
class Version20150901161334 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_tagbundle_tagged_item (
                id INT AUTO_INCREMENT NOT NULL, 
                tag_id INT NOT NULL, 
                item_id INT NOT NULL, 
                item_class VARCHAR(255) NOT NULL, 
                INDEX IDX_C8B7E80FBAD26311 (tag_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_tagbundle_tag (
                id INT AUTO_INCREMENT NOT NULL, 
                tag_name VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_6E5EC9DB02CC1B0 (tag_name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_tagbundle_tagged_item 
            ADD CONSTRAINT FK_C8B7E80FBAD26311 FOREIGN KEY (tag_id) 
            REFERENCES claro_tagbundle_tag (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_tagbundle_tagged_item 
            DROP FOREIGN KEY FK_C8B7E80FBAD26311
        ");
        $this->addSql("
            DROP TABLE claro_tagbundle_tagged_item
        ");
        $this->addSql("
            DROP TABLE claro_tagbundle_tag
        ");
    }
}