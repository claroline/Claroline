<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/03/28 10:37:20
 */
class Version20180328103718 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace
            ADD default_role_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace
            ADD CONSTRAINT FK_D9028545248673E9 FOREIGN KEY (default_role_id)
            REFERENCES claro_role (id)
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_D9028545248673E9 ON claro_workspace (default_role_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace
            DROP FOREIGN KEY FK_D9028545248673E9
        ');
        $this->addSql('
            DROP INDEX IDX_D9028545248673E9 ON claro_workspace
        ');
        $this->addSql('
            ALTER TABLE claro_workspace
            DROP default_role_id
        ');
    }
}
