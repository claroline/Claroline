<?php

namespace Icap\InwicastBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/03/30 03:25:05
 */
class Version20150330152503 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS inwicast_plugin_mediacenter (
                id INT AUTO_INCREMENT NOT NULL, 
                url VARCHAR(255) NOT NULL, 
                driver VARCHAR(255) NOT NULL, 
                host VARCHAR(255) NOT NULL, 
                port VARCHAR(255) NOT NULL, 
                dbname VARCHAR(255) NOT NULL, 
                user VARCHAR(255) NOT NULL, 
                password VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE IF NOT EXISTS inwicast_plugin_media (
                id INT AUTO_INCREMENT NOT NULL, 
                widgetinstance_id INT DEFAULT NULL, 
                mediaRef VARCHAR(255) NOT NULL, 
                preview_url VARCHAR(255) DEFAULT NULL, 
                width INT NOT NULL, 
                height INT NOT NULL, 
                UNIQUE INDEX UNIQ_ED925F022DE7D582 (widgetinstance_id), 
                PRIMARY KEY(id),
                CONSTRAINT FK_ED925F022DE7D582 FOREIGN KEY (widgetinstance_id) REFERENCES claro_widget_instance (id) 
                ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE inwicast_plugin_mediacenter
        ');
        $this->addSql('
            DROP TABLE inwicast_plugin_media
        ');
    }
}
