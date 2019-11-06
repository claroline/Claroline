<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/11/06 09:20:45
 */
class Version20191106092044 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_icon_set 
            ADD editable TINYINT(1) DEFAULT '0' NOT NULL, 
            ADD uuid VARCHAR(36) NOT NULL
        ");
        $this->addSql('
            UPDATE claro_icon_set SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_D91D0EED17F50A6 ON claro_icon_set (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_icon_item 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_icon_item SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_D727F16BD17F50A6 ON claro_icon_item (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_D727F16BD17F50A6 ON claro_icon_item
        ');
        $this->addSql('
            ALTER TABLE claro_icon_item 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_D91D0EED17F50A6 ON claro_icon_set
        ');
        $this->addSql('
            ALTER TABLE claro_icon_set 
            DROP editable, 
            DROP uuid
        ');
    }
}
