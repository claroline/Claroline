<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/03/23 06:14:08
 */
class Version20180323181407 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace
            ADD thumbnail_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace
            ADD CONSTRAINT FK_D9028545FDFF2E92 FOREIGN KEY (thumbnail_id)
            REFERENCES claro_public_file (id)
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_D9028545FDFF2E92 ON claro_workspace (thumbnail_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace
            DROP FOREIGN KEY FK_D9028545FDFF2E92
        ');
        $this->addSql('
            DROP INDEX IDX_D9028545FDFF2E92 ON claro_workspace
        ');
        $this->addSql('
            ALTER TABLE claro_workspace
            DROP thumbnail_id
        ');
    }
}
