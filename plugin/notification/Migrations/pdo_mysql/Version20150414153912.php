<?php

namespace Icap\NotificationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/04/14 03:39:15
 */
class Version20150414153912 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE icap__notification_plugin_configuration (
                id INT AUTO_INCREMENT NOT NULL, 
                dropdown_items INT NOT NULL, 
                max_per_page INT NOT NULL, 
                purge_enabled TINYINT(1) NOT NULL, 
                purge_after_days INT NOT NULL, 
                last_purge_date DATETIME DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE icap__notification_plugin_configuration
        ');
    }
}
