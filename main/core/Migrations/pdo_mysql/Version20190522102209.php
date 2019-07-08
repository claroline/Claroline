<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/05/22 10:22:11
 */
class Version20190522102209 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__organization
            ADD maxUsers INT NOT NULL
        ');
        $this->addSql('
            UPDATE claro__organization
            SET maxUsers = -1
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__organization
            DROP maxUsers
        ');
    }
}
