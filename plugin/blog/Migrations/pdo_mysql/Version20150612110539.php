<?php

namespace Icap\BlogBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/06/12 11:05:40
 */
class Version20150612110539 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE icap__blog_widget_list_options (
                id INT AUTO_INCREMENT NOT NULL, 
                displayStyle VARCHAR(1) NOT NULL, 
                widgetInstance_id INT NOT NULL, 
                UNIQUE INDEX UNIQ_987CAC1AB7B5A55 (widgetInstance_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE icap__blog_widget_list_options 
            ADD CONSTRAINT FK_987CAC1AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE icap__blog_widget_list_options
        ');
    }
}
