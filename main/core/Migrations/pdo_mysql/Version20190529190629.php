<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/05/29 07:06:30
 */
class Version20190529190629 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_import_file
            ADD workspace_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_import_file
            ADD CONSTRAINT FK_EA6FE9F182D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_EA6FE9F182D40A1F ON claro_import_file (workspace_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_import_file
            DROP FOREIGN KEY FK_EA6FE9F182D40A1F
        ');
        $this->addSql('
            DROP INDEX IDX_EA6FE9F182D40A1F ON claro_import_file
        ');
        $this->addSql('
            ALTER TABLE claro_import_file
            DROP workspace_id
        ');
    }
}
