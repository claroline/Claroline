<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/11/02 05:49:07
 */
class Version20221102054906 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD successCondition LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');

        // keep old behavior
        $this->addSql('
            UPDATE claro_workspace SET successCondition = \'{"maxFailed": 0}\' 
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP successCondition
        ');
    }
}
