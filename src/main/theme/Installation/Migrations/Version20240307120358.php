<?php

namespace Claroline\ThemeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/03/07 12:05:01
 */
final class Version20240307120358 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_theme 
            ADD font_size VARCHAR(255) DEFAULT NULL, 
            ADD font_weight VARCHAR(255) DEFAULT NULL, 
            ADD theme_mode VARCHAR(255) DEFAULT NULL,
            ADD logo VARCHAR(255) DEFAULT NULL
        ');

        $this->addSql('
            INSERT INTO claro_ordered_tool (uuid, context_name, tool_name, entity_order, fullscreen)
            VALUES ((SELECT UUID()), "account", "appearance", 2, 0)
        ');

        $this->addSql('
            CREATE TABLE claro_theme_user_preferences (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                list_display VARCHAR(255) DEFAULT NULL, 
                theme_mode VARCHAR(255) DEFAULT NULL, 
                font_size VARCHAR(255) DEFAULT NULL, 
                font_weight VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX IDX_C8605B7AA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_theme_user_preferences 
            ADD CONSTRAINT FK_C8605B7AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_theme 
            DROP font_size, 
            DROP font_weight, 
            DROP default_mode,
            DROP logo
        ');

        $this->addSql('
            ALTER TABLE claro_theme_user_preferences 
            DROP FOREIGN KEY FK_C8605B7AA76ED395
        ');
        $this->addSql('
            DROP TABLE claro_theme_user_preferences
        ');
        $this->addSql('
            ALTER TABLE claro_theme CHANGE theme_mode default_mode VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`
        ');
    }
}
