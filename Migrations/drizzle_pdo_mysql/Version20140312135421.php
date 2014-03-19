<?php

namespace Innova\PathBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/12 01:54:22
 */
class Version20140312135421 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_stepWho 
            ADD is_default BOOLEAN NOT NULL DEFAULT 0
        ");
        $this->addSql("
            ALTER TABLE innova_stepWhere 
            ADD is_default BOOLEAN NOT NULL DEFAULT 0
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_stepWhere 
            DROP is_default
        ");
        $this->addSql("
            ALTER TABLE innova_stepWho 
            DROP is_default
        ");
    }
}