<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/01/08 09:31:26
 */
class Version20160108093121 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_path_widget_config (
                id INT AUTO_INCREMENT NOT NULL, 
                status LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', 
                widgetInstance_id INT DEFAULT NULL, 
                INDEX IDX_C9025154AB7B5A55 (widgetInstance_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE innova_path_widget_config 
            ADD CONSTRAINT FK_C9025154AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE innova_path_widget_config
        ');
    }
}
