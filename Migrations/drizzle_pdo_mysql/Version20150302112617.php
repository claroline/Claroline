<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/02 11:26:20
 */
class Version20150302112617 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            DROP FOREIGN KEY FK_6824A65EF7A2C2FC
        ");
        $this->addSql("
            DROP INDEX IDX_6824A65EF7A2C2FC ON claro_activity_rule
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            DROP badge_id
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD badge_id INT DEFAULT NULL
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