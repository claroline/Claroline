<?php

namespace Claroline\ThemeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/05/10 12:14:55
 */
final class Version20230510121455 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE claro_color_collection (
                id INT AUTO_INCREMENT NOT NULL, 
                colors LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                uuid VARCHAR(36) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_E9FE9211D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_color_collection
        ');
    }
}
