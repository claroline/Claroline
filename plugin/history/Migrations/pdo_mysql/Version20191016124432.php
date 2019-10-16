<?php

namespace Claroline\HistoryBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/10/16 12:44:34
 */
class Version20191016124432 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_recent CHANGE createdAt createdAt DATETIME DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_recent CHANGE createdAt createdAt DATETIME DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_recent CHANGE createdAt createdAt DATETIME NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_recent CHANGE createdAt createdAt DATETIME NOT NULL
        ');
    }
}
