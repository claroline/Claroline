<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/10/10 10:11:58
 */
class Version20161010101157 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE INDEX name_idx ON claro_workspace (name)
        ');
        $this->addSql('
            CREATE INDEX code_idx ON claro_user (administrative_code)
        ');
        $this->addSql('
            CREATE INDEX enabled_idx ON claro_user (is_enabled)
        ');
        $this->addSql('
            CREATE INDEX name_idx ON claro__event (name)
        ');
        $this->addSql('
            CREATE INDEX mask_idx ON claro_resource_rights (mask)
        ');
        $this->addSql('
            CREATE INDEX action_idx ON claro_log (action)
        ');
        $this->addSql('
            CREATE INDEX tool_idx ON claro_log (tool_name)
        ');
        $this->addSql('
            CREATE INDEX doer_type_idx ON claro_log (doer_type)
        ');
        $this->addSql('
            CREATE INDEX name_idx ON claro_workspace_tag (name)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX name_idx ON claro__event
        ');
        $this->addSql('
            DROP INDEX action_idx ON claro_log
        ');
        $this->addSql('
            DROP INDEX tool_idx ON claro_log
        ');
        $this->addSql('
            DROP INDEX doer_type_idx ON claro_log
        ');
        $this->addSql('
            DROP INDEX mask_idx ON claro_resource_rights
        ');
        $this->addSql('
            DROP INDEX code_idx ON claro_user
        ');
        $this->addSql('
            DROP INDEX enabled_idx ON claro_user
        ');
        $this->addSql('
            DROP INDEX name_idx ON claro_workspace
        ');
        $this->addSql('
            DROP INDEX name_idx ON claro_workspace_tag
        ');
    }
}
