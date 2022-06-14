<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/06/14 11:25:22
 */
class Version20220614112516 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD estimatedDuration INT DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP estimatedDuration
        ');
    }
}
