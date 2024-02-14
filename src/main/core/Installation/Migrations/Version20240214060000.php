<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/02/14 06:00:00
 */
final class Version20240214060000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DELETE FROM claro_ordered_tool WHERE tool_name = "logs" AND context_name = "workspace"
        ');

        $this->addSql('
            DELETE FROM claro_ordered_tool WHERE tool_name = "claroline_team_tool" AND context_name = "workspace"
        ');

        $this->addSql('
            DELETE FROM claro_ordered_tool WHERE tool_name = "notification" AND context_name = "desktop"
        ');

        $this->addSql('
            DELETE FROM claro_ordered_tool WHERE tool_name = "ujm_questions" AND context_name = "desktop"
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
