<?php

namespace Icap\NotificationBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/09 03:00:14
 */
class Version20150409150013 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__notification_user_parameters (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                display_enabled_types TEXT NOT NULL COMMENT '(DC2Type:array)', 
                rss_enabled_types TEXT NOT NULL COMMENT '(DC2Type:array)', 
                rss_id VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_F44A756DA9D08426 (rss_id)
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__notification_user_parameters
        ");
    }
}