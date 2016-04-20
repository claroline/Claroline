<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/05/28 02:48:20
 */
class Version20150528144818 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_536FFC4C5E237E06 ON claro_workspace_model
        ');
        $this->addSql('
            CREATE UNIQUE INDEX workspace_model_unique_name_workspace ON claro_workspace_model (name, workspace_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX workspace_model_unique_name_workspace ON claro_workspace_model
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_536FFC4C5E237E06 ON claro_workspace_model (name)
        ');
    }
}
