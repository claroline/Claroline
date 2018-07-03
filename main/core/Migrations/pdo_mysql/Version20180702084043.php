<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/07/02 08:40:45
 */
class Version20180702084043 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_data_source (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                source_name VARCHAR(255) NOT NULL, 
                source_type VARCHAR(255) NOT NULL, 
                context LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)", 
                tags LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)", 
                INDEX IDX_B4A87F0BEC942BCF (plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_data_source 
            ADD CONSTRAINT FK_B4A87F0BEC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_data_source
        ');
    }
}
