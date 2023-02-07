<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/02/07 08:03:11
 */
class Version20230207080258 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD code VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_resource_node SET code = slug
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_A76799FF77153098 ON claro_resource_node (code)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP code
        ');
    }
}
