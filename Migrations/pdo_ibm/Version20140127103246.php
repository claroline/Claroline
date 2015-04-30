<?php

namespace UJM\ExoBundle\Migrations\pdo_ibm;

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
            ADD COLUMN selector SMALLINT DEFAULT NULL ALTER position \"position\" INTEGER DEFAULT NULL ALTER orthography orthography SMALLINT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_hole 
            DROP COLUMN selector ALTER position \"position\" INTEGER NOT NULL ALTER orthography orthography SMALLINT NOT NULL
        ");
    }
}