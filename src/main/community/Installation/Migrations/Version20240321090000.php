<?php

namespace Claroline\CommunityBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/02/26 06:46:16
 */
final class Version20240321090000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DELETE FROM claro_tools WHERE `name` = "profile"
        ');

        $this->addSql('
            DELETE FROM claro_ordered_tool WHERE `tool_name` = "profile"
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
