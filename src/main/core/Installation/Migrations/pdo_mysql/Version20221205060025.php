<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/12/05 06:00:34
 */
class Version20221205060025 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP estimatedDuration
        ");
        $this->addSql("
            ALTER TABLE claro_widget_container_config 
            DROP titleColor, 
            DROP backgroundColor, 
            DROP boxShadow, 
            DROP textColor, 
            DROP maxContentWidth, 
            DROP titleLevel, 
            DROP description, 
            DROP backgroundUrl, 
            DROP minHeight, 
            CHANGE layout layout LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
            CHANGE backgroundType backgroundType VARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_widget_container_config 
            ADD titleColor VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD backgroundColor VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD boxShadow VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD textColor VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD maxContentWidth VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD titleLevel SMALLINT DEFAULT NULL, 
            ADD description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD backgroundUrl VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD minHeight VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            CHANGE layout layout LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci` COMMENT '(DC2Type:json)', 
            CHANGE backgroundType backgroundType VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD estimatedDuration INT DEFAULT NULL
        ");
    }
}
