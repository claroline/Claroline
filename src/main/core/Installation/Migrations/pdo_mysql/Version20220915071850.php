<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/09/15 07:18:52
 */
class Version20220915071850 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX group_unique_name ON claro_group
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_E7C393D75E237E06 ON claro_group (name)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX uniq_e7c393d75e237e06 ON claro_group
        ');
        $this->addSql('
            CREATE UNIQUE INDEX group_unique_name ON claro_group (name)
        ');
    }
}
