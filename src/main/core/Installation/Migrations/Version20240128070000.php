<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/10/29 08:31:35
 */
final class Version20240128070000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_widget_container_config 
            ADD boxShadow VARCHAR(255) DEFAULT NULL, 
            ADD textColor VARCHAR(255) DEFAULT NULL,
            ADD maxContentWidth VARCHAR(255) DEFAULT NULL, 
            ADD titleLevel SMALLINT NOT NULL, 
            ADD description LONGTEXT DEFAULT NULL,
            ADD backgroundUrl VARCHAR(255) DEFAULT NULL, 
            ADD minHeight VARCHAR(255) DEFAULT NULL,  
            CHANGE background backgroundColor VARCHAR(255) DEFAULT NULL,
            CHANGE layout layout LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)",
            CHANGE color titleColor VARCHAR(255) DEFAULT NULL
        ');

        $this->addSql('
            UPDATE claro_widget_container_config SET titleLevel = 2
        ');

        $this->addSql('
            UPDATE claro_widget_container_config SET backgroundUrl = backgroundColor WHERE backgroundType = "image"
        ');

        $this->addSql('
            UPDATE claro_widget_container_config SET backgroundColor = null WHERE backgroundType = "image"
        ');

        $this->addSql('
            ALTER TABLE claro_widget_container_config
            DROP backgroundType
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_widget_container_config
            ADD backgroundType VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,   
            DROP boxShadow, 
            DROP textColor,
            DROP maxContentWidth, 
            DROP titleLevel, 
            DROP description,
            DROP backgroundUrl, 
            DROP minHeight,
            CHANGE backgroundColor background VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
            CHANGE layout layout LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)",
            CHANGE titleColor color VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`
        ');
    }
}
