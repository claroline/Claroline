<?php

namespace Icap\BlogBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/26 10:16:01
 */
class Version20141126101559 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__blog_widget_list (
                id INT AUTO_INCREMENT NOT NULL, 
                blog_id INT DEFAULT NULL, 
                widgetInstance_id INT DEFAULT NULL, 
                INDEX IDX_6979A1C3DAE07E97 (blog_id), 
                INDEX IDX_6979A1C3AB7B5A55 (widgetInstance_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE icap__blog_widget_list 
            ADD CONSTRAINT FK_6979A1C3DAE07E97 FOREIGN KEY (blog_id) 
            REFERENCES icap__blog (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__blog_widget_list 
            ADD CONSTRAINT FK_6979A1C3AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__blog_widget_list
        ");
    }
}