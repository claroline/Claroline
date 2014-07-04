<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/28 10:28:41
 */
class Version20140428102835 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace
            DROP FOREIGN KEY FK_D9028545727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_D9028545727ACA70 ON claro_workspace
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            DROP parent_id,
            DROP discr,
            DROP lft,
            DROP lvl,
            DROP rgt,
            DROP root
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace
            ADD parent_id INT DEFAULT NULL,
            ADD discr VARCHAR(255) NOT NULL,
            ADD lft INT DEFAULT NULL,
            ADD lvl INT DEFAULT NULL,
            ADD rgt INT DEFAULT NULL,
            ADD root INT DEFAULT NULL
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
