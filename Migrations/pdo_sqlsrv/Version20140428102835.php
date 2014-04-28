<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/28 10:28:42
 */
class Version20140428102835 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace
            DROP COLUMN parent_id
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            DROP COLUMN discr
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            DROP COLUMN lft
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            DROP COLUMN lvl
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            DROP COLUMN rgt
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            DROP COLUMN root
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            DROP CONSTRAINT FK_D9028545727ACA70
        ");
        $this->addSql("
            IF EXISTS (
                SELECT *
                FROM sysobjects
                WHERE name = 'IDX_D9028545727ACA70'
            )
            ALTER TABLE claro_workspace
            DROP CONSTRAINT IDX_D9028545727ACA70 ELSE
            DROP INDEX IDX_D9028545727ACA70 ON claro_workspace
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace
            ADD parent_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            ADD discr NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            ADD lft INT
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            ADD lvl INT
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            ADD rgt INT
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            ADD root INT
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            ADD CONSTRAINT FK_D9028545727ACA70 FOREIGN KEY (parent_id)
            REFERENCES claro_workspace (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545727ACA70 ON claro_workspace (parent_id)
        ");
    }
}
