<?php

namespace Claroline\ThemeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230510121456 extends AbstractMigration
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

        $colors = [
            '#FF6900',
            '#FCB900',
            '#7BDCB5',
            '#00D084',
            '#8ED1FC',
            '#0693E3',
            '#ABB8C3',
            '#EB144C',
            '#FFFFFF',
            '#000000',
        ];

        $colorsJson = json_encode($colors);

        $this->addSql("
            INSERT INTO claro_color_collection (colors, uuid, entity_name)
            VALUES (?, UUID(), 'Default')
        ", [$colorsJson]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_color_collection
        ');
    }
}
