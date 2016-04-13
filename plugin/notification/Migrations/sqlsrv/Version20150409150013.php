<?php

namespace Icap\NotificationBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/04/09 03:00:14
 */
class Version20150409150013 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE icap__notification_user_parameters (
                id INT IDENTITY NOT NULL, 
                user_id INT, 
                display_enabled_types VARCHAR(MAX) NOT NULL, 
                rss_enabled_types VARCHAR(MAX) NOT NULL, 
                rss_id NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_F44A756DA9D08426 ON icap__notification_user_parameters (rss_id) 
            WHERE rss_id IS NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE icap__notification_user_parameters
        ');
    }
}
