<?php

namespace Claroline\ThemeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/11/22 06:54:07
 */
class Version20221122065354 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_theme 
            DROP FOREIGN KEY FK_1D76301AA76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_1D76301AA76ED395 ON claro_theme
        ');
        $this->addSql('
            ALTER TABLE claro_theme 
            DROP user_id, 
            DROP enabled, 
            DROP extending_default, 
            CHANGE name entity_name VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_theme 
            ADD user_id INT DEFAULT NULL, 
            ADD enabled TINYINT(1) NOT NULL, 
            ADD extending_default TINYINT(1) NOT NULL, 
            CHANGE entity_name name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`
        ');
        $this->addSql('
            ALTER TABLE claro_theme 
            ADD CONSTRAINT FK_1D76301AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_1D76301AA76ED395 ON claro_theme (user_id)
        ');
    }
}
