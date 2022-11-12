<?php

namespace Claroline\ThemeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/11/18 08:36:03
 */
class Version20221118083553 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // just a little hack because we no longer allow null value for name
        $this->addSql('
            UPDATE claro_icon_item SET `name` = "" WHERE `name` IS NULL 
        ');

        $this->addSql('
            ALTER TABLE claro_icon_item 
            CHANGE `name` entity_name VARCHAR(255) NOT NULL, 
            DROP class
        ');
        $this->addSql('
            ALTER TABLE claro_icon_set 
            DROP is_active, 
            CHANGE name entity_name VARCHAR(255) NOT NULL, 
            CHANGE editable is_locked TINYINT(1) DEFAULT "0" NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_icon_item 
            CHANGE entity_name `name` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD class VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`
        ');
        $this->addSql('
            ALTER TABLE claro_icon_set 
            ADD is_active TINYINT(1) NOT NULL, 
            CHANGE entity_name `name` VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            CHANGE is_locked editable TINYINT(1) DEFAULT "0" NOT NULL
        ');
    }
}
