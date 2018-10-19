<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/10/23 12:59:31
 */
class Version20181023125927 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace_options 
            ADD breadcrumbItems LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json_array)"
        ');

        $this->addSql('
            UPDATE claro_workspace_options 
            SET breadcrumbItems = "[\'desktop\', \'workspaces\', \'current\', \'tool\']"
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace_options 
            DROP breadcrumbItems
        ');
    }
}
