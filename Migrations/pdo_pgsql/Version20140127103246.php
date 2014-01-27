<?php

namespace UJM\ExoBundle\Migrations\pdo_pgsql;

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
            ADD selector BOOLEAN DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_hole ALTER position 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_hole ALTER orthography 
            DROP NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_hole 
            DROP selector
        ");
        $this->addSql("
            ALTER TABLE ujm_hole ALTER position 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_hole ALTER orthography 
            SET 
                NOT NULL
        ");
    }
}