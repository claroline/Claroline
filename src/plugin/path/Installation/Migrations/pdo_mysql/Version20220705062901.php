<?php

namespace Innova\PathBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/07/05 06:29:29
 */
class Version20220705062901 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE claro_resource_node AS n
            LEFT JOIN innova_step AS s ON s.resource_id = n.id 
            SET n.evaluated = true
            WHERE s.id IS NOT NULL
              AND s.evaluated = true
        ');

        $this->addSql('
            ALTER TABLE innova_step 
            DROP evaluated
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_step 
            ADD evaluated TINYINT(1) NOT NULL
        ');
    }
}
