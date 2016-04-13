<?php

namespace Icap\NotificationBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/01/28 08:56:27
 */
class Version20140128085626 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            '
                        CREATE TABLE icap__notification_follower_resource (
                            id INTEGER NOT NULL,
                            hash VARCHAR(64) NOT NULL,
                            resource_class VARCHAR(255) NOT NULL,
                            resource_id INTEGER NOT NULL,
                            follower_id INTEGER NOT NULL,
                            PRIMARY KEY(id)
                        )
                    '
        );
        $this->addSql(
            '
                        CREATE TABLE icap__notification (
                            id INTEGER NOT NULL,
                            creation_date DATETIME NOT NULL,
                            user_id INTEGER DEFAULT NULL,
                            resource_id INTEGER DEFAULT NULL,
                            icon_key VARCHAR(255) DEFAULT NULL,
                            action_key VARCHAR(255) NOT NULL,
                            details CLOB DEFAULT NULL,
                            PRIMARY KEY(id)
                        )
                    '
        );
        $this->addSql(
            '
                        CREATE TABLE icap__notification_viewer (
                            id INTEGER NOT NULL,
                            notification_id INTEGER NOT NULL,
                            viewer_id INTEGER NOT NULL,
                            status BOOLEAN DEFAULT NULL,
                            PRIMARY KEY(id)
                        )
                    '
        );
        $this->addSql(
            '
                        CREATE INDEX IDX_DB60418BEF1A9D84 ON icap__notification_viewer (notification_id)
                    '
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            '
                        DROP TABLE icap__notification_follower_resource
                    '
        );
        $this->addSql(
            '
                        DROP TABLE icap__notification
                    '
        );
        $this->addSql(
            '
                        DROP TABLE icap__notification_viewer
                    '
        );
    }
}
