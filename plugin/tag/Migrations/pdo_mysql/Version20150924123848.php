<?php

namespace Claroline\TagBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/09/24 12:38:50
 */
class Version20150924123848 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_tagbundle_tagged_object (
                id INT AUTO_INCREMENT NOT NULL, 
                tag_id INT NOT NULL, 
                object_id INT NOT NULL, 
                object_class VARCHAR(255) NOT NULL, 
                object_name VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_1EA1E15DBAD26311 (tag_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_tagbundle_tag (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                tag_name VARCHAR(255) NOT NULL, 
                INDEX IDX_6E5EC9DA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_tagbundle_resources_tags_widget_config (
                id INT AUTO_INCREMENT NOT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                widgetInstance_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_5F11BDA4AB7B5A55 (widgetInstance_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
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
        $this->addSql('
            ALTER TABLE claro_tagbundle_resources_tags_widget_config 
            ADD CONSTRAINT FK_5F11BDA4AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
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
        $this->addSql('
            DROP TABLE claro_tagbundle_resources_tags_widget_config
        ');
    }
}
