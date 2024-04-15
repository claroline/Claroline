<?php

namespace Claroline\ThemeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/04/05 12:10:59
 */
final class Version20240405120958 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_theme_poster (
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_C777B571D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_theme 
            ADD primaryColor VARCHAR(255) DEFAULT NULL, 
            ADD secondaryColor VARCHAR(255) DEFAULT NULL,
            ADD is_disabled TINYINT(1) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_theme SET is_disabled = false
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_theme_poster
        ');
        $this->addSql('
            ALTER TABLE claro_theme 
            DROP primaryColor, 
            DROP secondaryColor,
            DROP is_disabled
        ');
    }
}
