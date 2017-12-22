<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/07/20 12:07:12
 */
class Version20170720120711 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE INDEX is_removed ON claro_user (is_removed)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX is_removed ON claro_user
        ');
    }
}
