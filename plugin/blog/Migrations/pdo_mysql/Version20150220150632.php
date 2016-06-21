<?php

namespace Icap\BlogBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/02/20 03:06:34
 */
class Version20150220150632 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE icap__blog_widget_blog (
                id INT AUTO_INCREMENT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                widgetInstance_id INT NOT NULL, 
                INDEX IDX_EDA40898B87FAB32 (resourceNode_id), 
                INDEX IDX_EDA40898AB7B5A55 (widgetInstance_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE icap__blog_widget_blog 
            ADD CONSTRAINT FK_EDA40898B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE icap__blog_widget_blog 
            ADD CONSTRAINT FK_EDA40898AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE icap__blog_widget_blog
        ');
    }
}
