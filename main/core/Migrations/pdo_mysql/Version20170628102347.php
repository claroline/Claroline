<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/06/28 10:23:48
 */
class Version20170628102347 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            DROP INDEX unique_version ON claro_version
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            CREATE UNIQUE INDEX unique_version ON claro_version (version, bundle, branch)
        ');
    }
}
