<?php

namespace Claroline\CoreBundle\Migrations\oci8;

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
            DROP (
                parent_id, discr, lft, lvl, rgt, root
            )
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            DROP CONSTRAINT FK_D9028545727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_D9028545727ACA70
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace
            ADD (
                parent_id NUMBER(10) DEFAULT NULL,
                discr VARCHAR2(255) NOT NULL,
                lft NUMBER(10) DEFAULT NULL,
                lvl NUMBER(10) DEFAULT NULL,
                rgt NUMBER(10) DEFAULT NULL,
                root NUMBER(10) DEFAULT NULL
            )
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
