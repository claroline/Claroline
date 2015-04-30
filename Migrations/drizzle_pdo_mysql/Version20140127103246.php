<?php

namespace UJM\ExoBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/27 10:32:48
 */
class Version20140127103246 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_hole 
            ADD selector BOOLEAN DEFAULT NULL, 
            CHANGE position `position` INT DEFAULT NULL, 
            CHANGE orthography orthography BOOLEAN DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_hole 
            DROP selector, 
            CHANGE position `position` INT NOT NULL, 
            CHANGE orthography orthography BOOLEAN NOT NULL
        ");
    }
}