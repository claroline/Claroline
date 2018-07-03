<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/07/02 08:16:52
 */
class Version20180702081650 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_widget_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                node_id INT DEFAULT NULL, 
                widgetInstance_id INT NOT NULL, 
                INDEX IDX_A128E64DAB7B5A55 (widgetInstance_id), 
                INDEX IDX_A128E64D460D9FD7 (node_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_widget_resource 
            ADD CONSTRAINT FK_A128E64DAB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_resource 
            ADD CONSTRAINT FK_A128E64D460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_widget 
            ADD sources LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)", 
            DROP abstract
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_widget_resource
        ');
        $this->addSql('
            ALTER TABLE claro_widget 
            ADD abstract TINYINT(1) NOT NULL, 
            DROP sources
        ');
    }
}
