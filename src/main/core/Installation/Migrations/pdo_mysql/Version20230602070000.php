<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Claroline\MigrationBundle\Migrations\ConditionalMigrationTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/06/02 07:00:00
 */
class Version20230602070000 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE claro_workspace_options SET breadcrumbItems = REPLACE(breadcrumbItems, "\'", \'"\')');
    }

    public function down(Schema $schema): void
    {
    }
}
