<?php

namespace Claroline\CoreBundle\Migrations\sqlanywhere;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/24 11:28:07
 */
class Version20140124112805 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_rule
            ADD CONSTRAINT FK_805FCB8F16F956BA FOREIGN KEY (associated_badge)
            REFERENCES claro_badge (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule
            ADD CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id)
            REFERENCES claro_badge (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8F16F956BA ON claro_badge_rule (associated_badge)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_rule
            DROP FOREIGN KEY FK_805FCB8FF7A2C2FC
        ");
        $this->addSql("
            DROP INDEX claro_badge_rule.IDX_805FCB8F16F956BA
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule
            ADD CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id)
            REFERENCES claro_badge (id)
            ON DELETE CASCADE
        ");
    }
}
