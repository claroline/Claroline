<?php

namespace Icap\NotificationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/10/10 10:13:58
 */
class Version20161010101357 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE INDEX viewer_idx ON icap__notification_viewer (viewer_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX viewer_idx ON icap__notification_viewer
        ');
    }
}
