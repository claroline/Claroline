<?php

namespace Icap\NotificationBundle\Migrations\sqlsrv;

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
                            id INT IDENTITY NOT NULL,
                            hash NVARCHAR(64) NOT NULL,
                            resource_class NVARCHAR(255) NOT NULL,
                            resource_id INT NOT NULL,
                            follower_id INT NOT NULL,
                            PRIMARY KEY (id)
                        )
                    '
        );
        $this->addSql(
            '
                        CREATE TABLE icap__notification (
                            id INT IDENTITY NOT NULL,
                            creation_date DATETIME2(6) NOT NULL,
                            user_id INT,
                            resource_id INT,
                            icon_key NVARCHAR(255),
                            action_key NVARCHAR(255) NOT NULL,
                            details VARCHAR(MAX),
                            PRIMARY KEY (id)
                        )
                    '
        );
        $this->addSql(
            "
                        EXEC sp_addextendedproperty N 'MS_Description',
                        N '(DC2Type:json_array)',
                        N 'SCHEMA',
                        dbo,
                        N 'TABLE',
                        icap__notification,
                        N 'COLUMN',
                        details
                    "
        );
        $this->addSql(
            '
                        CREATE TABLE icap__notification_viewer (
                            id INT IDENTITY NOT NULL,
                            notification_id INT NOT NULL,
                            viewer_id INT NOT NULL,
                            status BIT,
                            PRIMARY KEY (id)
                        )
                    '
        );
        $this->addSql(
            '
                        CREATE INDEX IDX_DB60418BEF1A9D84 ON icap__notification_viewer (notification_id)
                    '
        );
        $this->addSql(
            '
                        ALTER TABLE icap__notification_viewer
                        ADD CONSTRAINT FK_DB60418BEF1A9D84 FOREIGN KEY (notification_id)
                        REFERENCES icap__notification (id)
                        ON DELETE CASCADE
                    '
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            '
                        ALTER TABLE icap__notification_viewer
                        DROP CONSTRAINT FK_DB60418BEF1A9D84
                    '
        );
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
