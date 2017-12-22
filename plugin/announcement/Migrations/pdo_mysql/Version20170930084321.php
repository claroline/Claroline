<?php

namespace Claroline\AnnouncementBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/09/30 08:43:39
 */
class Version20170930084321 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_announcement 
            ADD uuid VARCHAR(36) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_announcement SET uuid = (SELECT UUID())
        ');

        $this->addSql('
            ALTER TABLE claro_announcement_aggregate 
            ADD uuid VARCHAR(36) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_announcement_aggregate SET uuid = (SELECT UUID())
        ');

        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_79BF2C8CD17F50A6 ON claro_announcement_aggregate (uuid)
        ');

        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_778754E3D17F50A6 ON claro_announcement (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_778754E3D17F50A6 ON claro_announcement
        ');
        $this->addSql('
            DROP INDEX UNIQ_79BF2C8CD17F50A6 ON claro_announcement_aggregate
        ');

        $this->addSql('
            ALTER TABLE claro_announcement 
            DROP uuid
        ');
        $this->addSql('
            ALTER TABLE claro_announcement_aggregate 
            DROP uuid
        ');
    }
}
