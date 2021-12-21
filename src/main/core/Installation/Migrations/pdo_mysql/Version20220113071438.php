<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/01/13 07:14:50
 */
class Version20220113071438 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD evaluated TINYINT(1) DEFAULT "0" NOT NULL, 
            ADD required TINYINT(1) DEFAULT "0" NOT NULL
        ');

        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            DROP required
        ');

        $this->addSql('
            UPDATE claro_resource_node AS n
            LEFT JOIN claro_workspace_required_resources AS r ON (n.id = r.resourcenode_id)
            SET required = 1
            WHERE r.requirements_id IS NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP evaluated, 
            DROP required
        ');

        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            ADD required TINYINT(1) NOT NULL
        ');
    }
}
