<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/04/26 06:15:08
 */
class Version20220426061454 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_tools 
            DROP is_workspace_required, 
            DROP is_desktop_required, 
            DROP is_exportable, 
            DROP is_configurable_in_workspace, 
            DROP is_configurable_in_desktop, 
            DROP is_locked_for_admin, 
            DROP is_anonymous_excluded
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_tools 
            ADD is_workspace_required TINYINT(1) NOT NULL, 
            ADD is_desktop_required TINYINT(1) NOT NULL, 
            ADD is_exportable TINYINT(1) NOT NULL, 
            ADD is_configurable_in_workspace TINYINT(1) NOT NULL, 
            ADD is_configurable_in_desktop TINYINT(1) NOT NULL, 
            ADD is_locked_for_admin TINYINT(1) NOT NULL, 
            ADD is_anonymous_excluded TINYINT(1) NOT NULL
        ');
    }
}
