<?php

namespace Claroline\EvaluationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/09/25 10:39:02
 */
final class Version20250929080000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE claro_widget_list SET filters = REPLACE(filters, "workspaceTags", "workspace.tags") WHERE filters LIKE "%workspaceTags%"
        ');

        $this->addSql('
            UPDATE claro_widget_list SET availableFilters = REPLACE(availableFilters, "workspaceTags", "workspace.tags") WHERE availableFilters LIKE "%workspaceTags%"
        ');

        $this->addSql('
            UPDATE claro_widget_list SET displayedColumns = REPLACE(displayedColumns, "workspaceTags", "workspace.tags") WHERE displayedColumns LIKE "%workspaceTags%"
        ');

        $this->addSql('
            UPDATE claro_widget_list SET availableColumns = REPLACE(availableColumns, "workspaceTags", "workspace.tags") WHERE availableColumns LIKE "%workspaceTags%"
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
