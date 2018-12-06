<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/11/26 09:45:37
 */
class Version20181126094536 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_template_type (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                type_name VARCHAR(255) NOT NULL, 
                default_template VARCHAR(255) DEFAULT NULL,
                placeholders LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_7428AC44D17F50A6 (uuid), 
                INDEX IDX_7428AC44EC942BCF (plugin_id), 
                UNIQUE INDEX template_unique_type (type_name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_template (
                id INT AUTO_INCREMENT NOT NULL, 
                claro_template_type INT NOT NULL, 
                template_name VARCHAR(255) NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                content LONGTEXT NOT NULL, 
                lang VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_DFB26A75D17F50A6 (uuid), 
                INDEX IDX_DFB26A757428AC44 (claro_template_type), 
                UNIQUE INDEX template_unique_name (template_name, lang), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_template_type 
            ADD CONSTRAINT FK_7428AC44EC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_template 
            ADD CONSTRAINT FK_DFB26A757428AC44 FOREIGN KEY (claro_template_type) 
            REFERENCES claro_template_type (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_template 
            DROP FOREIGN KEY FK_DFB26A757428AC44
        ');
        $this->addSql('
            DROP TABLE claro_template_type
        ');
        $this->addSql('
            DROP TABLE claro_template
        ');
    }
}
