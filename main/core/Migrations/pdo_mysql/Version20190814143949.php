<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/08/14 02:39:50
 */
class Version20190814143949 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace
            ADD archived TINYINT(1) DEFAULT NULL
        ');

        $this->addSql('
            UPDATE claro_workspace SET archived = false
        ');
    }

    public function down(Schema $schema)
    {
    }
}
