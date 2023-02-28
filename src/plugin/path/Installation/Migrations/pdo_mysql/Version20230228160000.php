<?php

namespace Innova\PathBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/01/16 11:03:41
 */
class Version20230228160000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE claro_resource_node
            SET required = true
            WHERE evaluated = true
              AND required = false
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
