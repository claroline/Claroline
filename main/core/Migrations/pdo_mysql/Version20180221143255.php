<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/02/21 02:32:56
 */
class Version20180221143255 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace
            ADD slug VARCHAR(128) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_workspace SET slug = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_D9028545989D9B62 ON claro_workspace (slug)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_D9028545989D9B62 ON claro_workspace
        ');
        $this->addSql('
            ALTER TABLE claro_workspace
            DROP slug
        ');
    }
}
