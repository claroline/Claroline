<?php

namespace Icap\NotificationBundle\Migrations\pdo_pgsql;

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
                id SERIAL NOT NULL, 
                user_id INT DEFAULT NULL, 
                display_enabled_types TEXT NOT NULL, 
                rss_enabled_types TEXT NOT NULL, 
                rss_id VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F44A756DA9D08426 ON icap__notification_user_parameters (rss_id)
        ");
        $this->addSql("
            COMMENT ON COLUMN icap__notification_user_parameters.display_enabled_types IS '(DC2Type:array)'
        ");
        $this->addSql("
            COMMENT ON COLUMN icap__notification_user_parameters.rss_enabled_types IS '(DC2Type:array)'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__notification_user_parameters
        ");
    }
}