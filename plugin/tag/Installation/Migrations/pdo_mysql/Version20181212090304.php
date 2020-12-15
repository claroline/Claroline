<?php

namespace Claroline\TagBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 08:23:20
 */
class Version20181212090304 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_tagbundle_tagged_object (
                id INT AUTO_INCREMENT NOT NULL, 
                tag_id INT NOT NULL, 
                object_id VARCHAR(255) NOT NULL, 
                object_class VARCHAR(255) NOT NULL, 
                object_name VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_1EA1E15DBAD26311 (tag_id), 
                UNIQUE INDEX `unique` (object_id, object_class, tag_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_tagbundle_tag (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                tag_name VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                color VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_6E5EC9DD17F50A6 (uuid), 
                INDEX IDX_6E5EC9DA76ED395 (user_id), 
                UNIQUE INDEX `unique` (tag_name, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_tagbundle_tagged_object 
            ADD CONSTRAINT FK_1EA1E15DBAD26311 FOREIGN KEY (tag_id) 
            REFERENCES claro_tagbundle_tag (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_tagbundle_tag 
            ADD CONSTRAINT FK_6E5EC9DA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_tagbundle_tagged_object 
            DROP FOREIGN KEY FK_1EA1E15DBAD26311
        ');
        $this->addSql('
            DROP TABLE claro_tagbundle_tagged_object
        ');
        $this->addSql('
            DROP TABLE claro_tagbundle_tag
        ');
    }
}
