<?php

namespace Claroline\ThemeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230510121456 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
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
        $this->addSql("
            DELETE FROM claro_color_collection WHERE entity_name = 'Default'
        ");
    }
}
