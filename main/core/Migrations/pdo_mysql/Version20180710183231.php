<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/07/10 06:32:32
 */
class Version20180710183231 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_home_tab
            ADD centerTitle TINYINT(1) NOT NULL,
            CHANGE longTitle longTitle LONGTEXT
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance
            DROP display,
            DROP availableDisplays
        ');
        $this->addSql("
            ALTER TABLE claro_widget_list
            ADD display VARCHAR(255) NOT NULL,
            ADD availableDisplays LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_home_tab
            DROP centerTitle,
            CHANGE longTitle longTitle LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql("
            ALTER TABLE claro_widget_instance
            ADD display VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci,
            ADD availableDisplays LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)'
        ");
        $this->addSql('
            ALTER TABLE claro_widget_list
            DROP display,
            DROP availableDisplays
        ');
    }
}
