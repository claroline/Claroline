<?php

namespace Claroline\ThemeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240318090000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            INSERT INTO claro_ordered_tool (uuid, context_name, tool_name, entity_order, fullscreen)
            VALUES ((SELECT UUID()), "administration", "appearance", 2, 0)
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
