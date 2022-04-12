<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/05/03 06:52:23
 */
class Version20220503065222 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            ADD hidden TINYINT(1) DEFAULT "0" NOT NULL, 
            CHANGE display_order entity_order INT NOT NULL
        ');

        // reset order for all tools to avoid changing behavior for existing platforms (currently it's ordered alphabetically)
        $this->addSql('
            UPDATE claro_ordered_tool SET entity_order = 0
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            DROP hidden, 
            CHANGE entity_order display_order INT NOT NULL
        ');
    }
}
