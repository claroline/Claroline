<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/07/02 07:55:51
 */
class Version20180702075549 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_widget_container (
                id INT AUTO_INCREMENT NOT NULL, 
                widget_name VARCHAR(255) DEFAULT NULL, 
                color VARCHAR(255) DEFAULT NULL, 
                backgroundType VARCHAR(255) NOT NULL, 
                background VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_3B06DD75D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            ADD container_id INT DEFAULT NULL, 
            ADD widget_position INT NOT NULL, 
            DROP color, 
            DROP backgroundType, 
            DROP background, 
            CHANGE widget_name widget_name VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A385BC21F742 FOREIGN KEY (container_id) 
            REFERENCES claro_widget_container (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_5F89A385BC21F742 ON claro_widget_instance (container_id)
        ');
        $this->addSql('
            ALTER TABLE claro_widget CHANGE plugin_id plugin_id INT DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            DROP FOREIGN KEY FK_5F89A385BC21F742
        ');
        $this->addSql('
            DROP TABLE claro_widget_container
        ');
        $this->addSql('
            ALTER TABLE claro_widget CHANGE plugin_id plugin_id INT NOT NULL
        ');
        $this->addSql('
            DROP INDEX IDX_5F89A385BC21F742 ON claro_widget_instance
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            ADD color VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
            ADD backgroundType VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
            ADD background VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
            DROP container_id, 
            DROP widget_position, 
            CHANGE widget_name widget_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ');
    }
}
