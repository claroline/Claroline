<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

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
            DROP COLUMN discr
            DROP COLUMN lft
            DROP COLUMN lvl
            DROP COLUMN rgt
            DROP COLUMN root
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            DROP FOREIGN KEY FK_D9028545727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_D9028545727ACA70
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace
            ADD COLUMN parent_id INTEGER DEFAULT NULL
            ADD COLUMN discr VARCHAR(255) NOT NULL
            ADD COLUMN lft INTEGER DEFAULT NULL
            ADD COLUMN lvl INTEGER DEFAULT NULL
            ADD COLUMN rgt INTEGER DEFAULT NULL
            ADD COLUMN root INTEGER DEFAULT NULL
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
