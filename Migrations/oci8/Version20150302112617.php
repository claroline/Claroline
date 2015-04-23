<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/02 11:26:19
 */
class Version20150302112617 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            DROP CONSTRAINT FK_6824A65EF7A2C2FC
        ");
        $this->addSql("
            DROP INDEX IDX_6824A65EF7A2C2FC
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            DROP (badge_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD (
                badge_id NUMBER(10) DEFAULT NULL NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD CONSTRAINT FK_6824A65EF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6824A65EF7A2C2FC ON claro_activity_rule (badge_id)
        ");
    }
}