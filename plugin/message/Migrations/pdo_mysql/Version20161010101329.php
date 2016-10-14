<?php

namespace Claroline\MessageBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/10/10 10:13:30
 */
class Version20161010101329 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE INDEX level_idx ON claro_message (lvl)
        ');
        $this->addSql('
            CREATE INDEX root_idx ON claro_message (root)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX level_idx ON claro_message
        ');
        $this->addSql('
            DROP INDEX root_idx ON claro_message
        ');
    }
}
